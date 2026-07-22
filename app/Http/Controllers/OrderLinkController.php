<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\WhatsAppNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Commande par lien partagé : une page publique qu'un agent envoie au
 * client. Celui-ci choisit librement plusieurs produits, indique où livrer,
 * valide — un agent le rappelle ensuite. Aucun paiement en ligne, aucun
 * compte requis.
 */
class OrderLinkController extends Controller
{
    /** Frais de livraison forfaitaires, en FCFA. */
    private const FRAIS_LIVRAISON = 1000;

    /** Page de commande : catalogue + panier + formulaire. */
    public function create(): Response
    {
        $produits = Product::with('categories')
            ->where('status', 'Success')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'price' => (float) $p->price,
                'image' => $this->mediaUrl($p->img_1),
                'category' => $p->categories?->name,
            ]);

        return Inertia::render('order-link/index', [
            'produits' => $produits,
        ]);
    }

    /**
     * Enregistre la commande (statut « pending ») et prévient l'entreprise.
     * Le montant est recalculé serveur : jamais celui envoyé par le client.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_location' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ], [
            'items.required' => 'Ajoutez au moins un produit à votre commande.',
            'delivery_location.required' => 'Indiquez le lieu de livraison.',
        ]);

        // Produits rechargés depuis la base : prix et nom de confiance.
        $ids = collect($validated['items'])->pluck('id');
        $produits = Product::whereIn('id', $ids)->where('status', 'Success')->get()->keyBy('id');

        $lignes = [];
        $total = 0.0;

        foreach ($validated['items'] as $item) {
            $produit = $produits->get($item['id']);
            if (! $produit) {
                continue; // produit devenu indisponible : ignoré
            }
            $sousTotal = (float) $produit->price * $item['quantity'];
            $total += $sousTotal;
            $lignes[] = ['produit' => $produit, 'quantite' => $item['quantity'], 'sous_total' => $sousTotal];
        }

        if ($lignes === []) {
            return response()->json([
                'ok' => false,
                'message' => 'Aucun des produits sélectionnés n\'est disponible.',
            ], 422);
        }

        // Frais de livraison forfaitaires ajoutés au sous-total des produits.
        $sousTotal = $total;
        $totalTTC = $sousTotal + self::FRAIS_LIVRAISON;

        $sale = DB::transaction(function () use ($validated, $lignes, $sousTotal, $totalTTC) {
            $sale = Sale::create([
                'ref' => 'CMD_'.strtoupper(Str::random(10)),
                'sale_date' => Carbon::now(),
                'id_user' => null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_location' => $validated['delivery_location'],
                'subtotal' => $sousTotal,
                'discount' => 0,
                // Le total inclut les frais de livraison ; ces frais tiennent
                // dans « discount » négatif serait trompeur, on les porte donc
                // dans le total et on les redétaille sur la facture.
                'total' => $totalTTC,
                'payment_method' => 'agent',
                'channel' => 'lien',
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lignes as $ligne) {
                SaleItem::create([
                    'id_sale' => $sale->id,
                    'id_product' => $ligne['produit']->id,
                    'product_name' => $ligne['produit']->name,
                    'quantity' => $ligne['quantite'],
                    'unit_price' => (float) $ligne['produit']->price,
                    'subtotal' => $ligne['sous_total'],
                ]);
            }

            return $sale;
        });

        // Alerte à l'entreprise, sans jamais faire échouer la commande.
        try {
            WhatsAppNotifier::send($this->messageEntreprise($sale, $lignes));
        } catch (\Throwable) {
            // journalisé par le notifier
        }

        return response()->json([
            'ok' => true,
            'reference' => $sale->ref,
            'sous_total_formate' => number_format($sousTotal, 0, ',', ' ').' FCFA',
            'livraison_formate' => number_format(self::FRAIS_LIVRAISON, 0, ',', ' ').' FCFA',
            'total_formate' => number_format($totalTTC, 0, ',', ' ').' FCFA',
            // Lien de téléchargement de la facture PDF (référence = code unique)
            'facture_url' => route('order-link.invoice', $sale->ref),
            'message' => 'Votre commande a bien été prise en compte. Un agent va vous contacter très bientôt.',
        ], 201);
    }

    /**
     * Facture PDF téléchargeable, identifiée par la référence unique de la
     * commande. Publique mais protégée par cette référence imprévisible :
     * seul le client qui l'a reçue peut la récupérer.
     */
    public function invoice(string $reference)
    {
        $sale = Sale::with('items')->where('ref', $reference)->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.order', [
            'sale' => $sale,
            'frais_livraison' => self::FRAIS_LIVRAISON,
        ])->setPaper('a4');

        return $pdf->download('facture-'.$sale->ref.'.pdf');
    }

    private function messageEntreprise(Sale $sale, array $lignes): string
    {
        $produits = collect($lignes)
            ->map(fn ($l) => "• {$l['quantite']} × {$l['produit']->name}")
            ->implode("\n");

        return "🛒 *Nouvelle commande (lien)*\n"
            ."Réf : {$sale->ref}\n"
            ."Client : {$sale->customer_name}\n"
            ."Tél : {$sale->customer_phone}\n"
            ."Livraison : {$sale->delivery_location}\n"
            ."Produits :\n{$produits}\n"
            ."Total : ".number_format((float) $sale->total, 0, ',', ' ')." FCFA\n"
            .($sale->notes ? "Note : {$sale->notes}\n" : '')
            ."➡️ À rappeler.";
    }
}
