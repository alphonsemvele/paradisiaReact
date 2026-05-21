<?php

namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicStatsController extends Controller
{
    /**
     * 🏪 Liste publique des points de vente
     */
    public function pointsDeVente(): Response
    {
        $points = PointDeVente::where('status', 'Success')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'address' => $p->address,
                'phone' => $p->phone,
                'hours' => $p->hours,
                'lat' => (float) $p->latitude,
                'lng' => (float) $p->longitude,
                'has_location' => $p->latitude && $p->longitude,
                'image' => $p->image ? asset($p->image) : null,
            ]);

        return Inertia::render('dashboard/points-de-vente/index', [
            'points' => $points,
            'totalPoints' => $points->count(),
        ]);
    }

    /**
     * 🏪 Détail public d'un point de vente avec ses stats
     */
    public function pointDeVenteShow(PointDeVente $pointDeVente): Response
    {
        if ($pointDeVente->status !== 'Success') {
            abort(404);
        }

        // Stats ventes de ce point
        $totalSales = Sale::where('id_point_de_vente', $pointDeVente->id)
            ->where('status', 'Success')
            ->count();

        $totalCA = (float) Sale::where('id_point_de_vente', $pointDeVente->id)
            ->where('status', 'Success')
            ->sum('total');

        $monthCA = (float) Sale::where('id_point_de_vente', $pointDeVente->id)
            ->where('status', 'Success')
            ->where('sale_date', '>=', Carbon::now()->startOfMonth())
            ->sum('total');

        // Top produits vendus dans ce point
        $topProducts = SaleItem::select(
                'id_product',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_ca'),
            )
            ->whereHas('sale', function ($q) use ($pointDeVente) {
                $q->where('id_point_de_vente', $pointDeVente->id)
                  ->where('status', 'Success');
            })
            ->groupBy('id_product', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(function ($i) {
                $product = Product::find($i->id_product);
                return [
                    'id' => $i->id_product,
                    'name' => $i->product_name,
                    'image' => $product?->img_1 ? asset($product->img_1) : null,
                    'qty' => (int) $i->total_qty,
                ];
            });

        // Graphique CA 30j de ce point
        $chart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chart[] = [
                'label' => $date->format('d/m'),
                'ca' => (float) Sale::where('id_point_de_vente', $pointDeVente->id)
                    ->where('status', 'Success')
                    ->whereDate('sale_date', $date)
                    ->sum('total'),
            ];
        }

        return Inertia::render('dashboard/points-de-vente/show', [
            'point' => [
                'id' => $pointDeVente->id,
                'name' => $pointDeVente->name,
                'address' => $pointDeVente->address,
                'phone' => $pointDeVente->phone,
                'hours' => $pointDeVente->hours,
                'lat' => (float) $pointDeVente->latitude,
                'lng' => (float) $pointDeVente->longitude,
                'has_location' => $pointDeVente->latitude && $pointDeVente->longitude,
                'image' => $pointDeVente->image ? asset($pointDeVente->image) : null,
            ],
            'stats' => [
                'total_sales' => $totalSales,
                'total_ca' => $totalCA,
                'month_ca' => $monthCA,
                'top_products' => $topProducts,
                'chart' => $chart,
            ],
        ]);
    }

    /**
     * 📊 Statistiques globales publiques
     */
    public function statistiques(): Response
    {
        $totalSales = Sale::where('status', 'Success')->count();
        $totalCA = (float) Sale::where('status', 'Success')->sum('total');
        $totalProducts = Product::count();
        $totalPoints = PointDeVente::where('status', 'Success')->count();

        // Graphique CA 30j
        $chart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chart[] = [
                'label' => $date->format('d/m'),
                'ca' => (float) Sale::where('status', 'Success')
                    ->whereDate('sale_date', $date)
                    ->sum('total'),
                'count' => Sale::where('status', 'Success')
                    ->whereDate('sale_date', $date)
                    ->count(),
            ];
        }

        // Top 10 produits
        $topProducts = SaleItem::select(
                'id_product',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
            )
            ->whereHas('sale', fn ($q) => $q->where('status', 'Success'))
            ->groupBy('id_product', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($i) {
                $product = Product::find($i->id_product);
                return [
                    'id' => $i->id_product,
                    'name' => $i->product_name,
                    'image' => $product?->img_1 ? asset($product->img_1) : null,
                    'qty' => (int) $i->total_qty,
                ];
            });

        // Top points de vente
        $topPoints = Sale::select(
                'id_point_de_vente',
                DB::raw('SUM(total) as total_ca'),
                DB::raw('COUNT(*) as sales_count'),
            )
            ->where('status', 'Success')
            ->whereNotNull('id_point_de_vente')
            ->groupBy('id_point_de_vente')
            ->orderByDesc('total_ca')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                $point = PointDeVente::find($s->id_point_de_vente);
                return [
                    'id' => $s->id_point_de_vente,
                    'name' => $point?->name ?? 'Inconnu',
                    'image' => $point?->image ? asset($point->image) : null,
                    'sales' => (int) $s->sales_count,
                    'ca' => (float) $s->total_ca,
                ];
            });

        return Inertia::render('dashboard/statistiques/index', [
            'kpis' => [
                'total_sales' => $totalSales,
                'total_ca' => $totalCA,
                'total_products' => $totalProducts,
                'total_points' => $totalPoints,
            ],
            'chart' => $chart,
            'topProducts' => $topProducts,
            'topPoints' => $topPoints,
        ]);
    }

    /**
     * 📦 Stats publique d'un produit (actif ou non)
     */
    public function productStats(Product $product): Response
    {
        // On AUTORISE tous les statuts (actif ou non)

        // Stats de ventes du produit
        $totalQty = (int) SaleItem::where('id_product', $product->id)
            ->whereHas('sale', fn ($q) => $q->where('status', 'Success'))
            ->sum('quantity');

        $totalCA = (float) SaleItem::where('id_product', $product->id)
            ->whereHas('sale', fn ($q) => $q->where('status', 'Success'))
            ->sum('subtotal');

        $monthQty = (int) SaleItem::where('id_product', $product->id)
            ->whereHas('sale', function ($q) {
                $q->where('status', 'Success')
                  ->where('sale_date', '>=', Carbon::now()->startOfMonth());
            })
            ->sum('quantity');

        $monthCA = (float) SaleItem::where('id_product', $product->id)
            ->whereHas('sale', function ($q) {
                $q->where('status', 'Success')
                  ->where('sale_date', '>=', Carbon::now()->startOfMonth());
            })
            ->sum('subtotal');

        // Graphique 30j (qty vendue par jour)
        $chart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $qty = (int) SaleItem::where('id_product', $product->id)
                ->whereHas('sale', function ($q) use ($date) {
                    $q->where('status', 'Success')->whereDate('sale_date', $date);
                })
                ->sum('quantity');
            $chart[] = [
                'label' => $date->format('d/m'),
                'qty' => $qty,
            ];
        }

        // Répartition par point de vente
        $byPoint = SaleItem::select(
                'sales.id_point_de_vente',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_ca'),
            )
            ->join('sales', 'sales.id', '=', 'sale_items.id_sale')
            ->where('sale_items.id_product', $product->id)
            ->where('sales.status', 'Success')
            ->groupBy('sales.id_point_de_vente')
            ->orderByDesc('total_qty')
            ->get()
            ->map(function ($s) {
                $point = $s->id_point_de_vente ? PointDeVente::find($s->id_point_de_vente) : null;
                return [
                    'id' => $s->id_point_de_vente,
                    'name' => $point?->name ?? 'Vente particulière',
                    'qty' => (int) $s->total_qty,
                    'ca' => (float) $s->total_ca,
                ];
            });

        return Inertia::render('dashboard/products/stats', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'image' => $product->img_1 ? asset($product->img_1) : null,
                'image_2' => $product->img_2 ? asset($product->img_2) : null,
                'status' => $product->status,
                'is_active' => $product->status === 'Success',
                'category' => $product->categories ? [
                    'id' => $product->categories->id,
                    'name' => $product->categories->name,
                ] : null,
            ],
            'stats' => [
                'total_qty' => $totalQty,
                'total_ca' => $totalCA,
                'month_qty' => $monthQty,
                'month_ca' => $monthCA,
            ],
            'chart' => $chart,
            'byPoint' => $byPoint,
        ]);
    }
}