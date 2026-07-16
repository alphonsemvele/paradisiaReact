<?php

namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\WhatsAppNotifier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Commande côté client : page de validation du panier, création de la vente
 * (statut "pending", à confirmer par l'admin dans Ventes), confirmation.
 */
class OrderController extends Controller
{
    public function checkout(): Response|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop')->withErrors(['error' => 'Votre panier est vide']);
        }

        return Inertia::render('dashboard/shop/checkout', [
            'cart' => $cart,
            'pointsDeVente' => PointDeVente::where('status', 'Success')
                ->orderBy('name')
                ->get(['id', 'name', 'address', 'phone'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'address' => $p->address,
                    'phone' => $p->phone,
                ]),
            'customer' => [
                'name' => Auth::user()->name,
                'phone' => Auth::user()->phone ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop')->withErrors(['error' => 'Votre panier est vide']);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'id_point_de_vente' => 'nullable|exists:points_de_vente,id',
            'payment_method' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Prix relus en base : le panier en session n'est pas une source de confiance.
        $products = Product::whereIn('id', collect($cart)->pluck('id'))->get()->keyBy('id');

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $lines = [];

            foreach ($cart as $item) {
                $product = $products->get($item['id']);

                if (! $product) {
                    continue;
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineTotal,
                ];
            }

            if ($lines === []) {
                DB::rollBack();

                return redirect()->route('shop')->withErrors(['error' => 'Votre panier est vide']);
            }

            $sale = Sale::create([
                'ref' => 'CMD_'.strtoupper(Str::random(10)),
                'sale_date' => Carbon::now(),
                'id_user' => Auth::id(),
                'id_point_de_vente' => $validated['id_point_de_vente'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                SaleItem::create([
                    'id_sale' => $sale->id,
                    'id_product' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['product']->price,
                    'subtotal' => $line['subtotal'],
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Erreur lors de la commande : '.$e->getMessage()]);
        }

        session()->forget('cart');

        WhatsAppNotifier::send(self::orderMessage($sale, $lines));

        return redirect()->route('orders.confirmation', $sale->ref);
    }

    /**
     * Message WhatsApp envoyé à l'entreprise à chaque commande.
     */
    private static function orderMessage(Sale $sale, array $lines): string
    {
        $items = collect($lines)
            ->map(fn ($l) => "- {$l['quantity']} x {$l['product']->name} = ".number_format($l['subtotal'], 0, ',', ' ').' F')
            ->implode("\n");

        $paiement = $sale->payment_method === 'mobile_money' ? 'Mobile Money' : 'À la livraison / retrait';
        $retrait = $sale->pointDeVente?->name ?? 'Non précisé';

        return "🛒 NOUVELLE COMMANDE {$sale->ref}\n"
            ."Client : {$sale->customer_name}\n"
            ."Tél : {$sale->customer_phone}\n"
            ."Point de vente : {$retrait}\n"
            ."Paiement : {$paiement}\n"
            .$items."\n"
            .'TOTAL : '.number_format($sale->total, 0, ',', ' ').' FCFA'
            .($sale->notes ? "\nNotes : {$sale->notes}" : '');
    }

    public function confirmation(string $ref): Response
    {
        $sale = Sale::with(['items', 'pointDeVente'])
            ->where('ref', $ref)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        return Inertia::render('dashboard/shop/confirmation', [
            'order' => [
                'ref' => $sale->ref,
                'date' => $sale->sale_date->format('d/m/Y H:i'),
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'payment_method' => $sale->payment_method,
                'status' => $sale->status,
                'total' => $sale->total,
                'notes' => $sale->notes,
                'point_de_vente' => $sale->pointDeVente ? [
                    'name' => $sale->pointDeVente->name,
                    'address' => $sale->pointDeVente->address,
                    'phone' => $sale->pointDeVente->phone,
                ] : null,
                'items' => $sale->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]),
            ],
        ]);
    }
}
