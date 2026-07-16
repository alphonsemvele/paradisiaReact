<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointDeVente;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    /**
     * 📊 Dashboard statistiques
     */
    public function dashboard(): Response
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $todayCA = (float) Sale::whereDate('sale_date', $today)
            ->where('status', 'Success')
            ->sum('total');

        $yesterdayCA = (float) Sale::whereDate('sale_date', $yesterday)
            ->where('status', 'Success')
            ->sum('total');

        $monthCA = (float) Sale::where('sale_date', '>=', $thisMonth)
            ->where('status', 'Success')
            ->sum('total');

        $lastMonthCA = (float) Sale::whereBetween('sale_date', [$lastMonth, $thisMonth])
            ->where('status', 'Success')
            ->sum('total');

        $totalCA = (float) Sale::where('status', 'Success')->sum('total');

        $todaySales = Sale::whereDate('sale_date', $today)
            ->where('status', 'Success')
            ->count();

        $totalSales = Sale::where('status', 'Success')->count();

        $avgBasket = $totalSales > 0 ? $totalCA / $totalSales : 0;

        $dayGrowth = $yesterdayCA > 0
            ? round((($todayCA - $yesterdayCA) / $yesterdayCA) * 100, 1)
            : 0;

        $monthGrowth = $lastMonthCA > 0
            ? round((($monthCA - $lastMonthCA) / $lastMonthCA) * 100, 1)
            : 0;

        return Inertia::render('admin/sales/dashboard', [
            'kpis' => [
                'today_ca' => $todayCA,
                'today_sales' => $todaySales,
                'day_growth' => $dayGrowth,
                'month_ca' => $monthCA,
                'month_growth' => $monthGrowth,
                'total_ca' => $totalCA,
                'total_sales' => $totalSales,
                'avg_basket' => $avgBasket,
            ],

            'salesChart' => $this->getSalesChart(),
            'topProducts' => $this->getTopProducts(),
            'topPointsDeVente' => $this->getTopPointsDeVente(),
            'topCategories' => $this->getTopCategories(),
            'paymentMethods' => $this->getPaymentMethodsBreakdown(),
            'recentSales' => $this->getRecentSales(),
        ]);
    }

    /**
     * 📋 Liste des ventes
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $pointId = $request->get('point');
        $paymentMethod = $request->get('payment_method');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Sale::with(['user', 'pointDeVente', 'items']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ref', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($pointId) {
            $query->where('id_point_de_vente', $pointId);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($dateFrom) {
            $query->whereDate('sale_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('sale_date', '<=', $dateTo);
        }

        $sales = $query->orderByDesc('sale_date')
            ->paginate(15)
            ->withQueryString();

        $sales->getCollection()->transform(fn ($s) => $this->formatSaleListItem($s));

        $stats = [
            'total' => Sale::where('status', 'Success')->count(),
            'today' => Sale::whereDate('sale_date', Carbon::today())->where('status', 'Success')->count(),
            'week_ca' => (float) Sale::where('sale_date', '>=', Carbon::now()->subWeek())
                ->where('status', 'Success')
                ->sum('total'),
            'pending' => Sale::where('status', 'pending')->count(),
        ];

        return Inertia::render('admin/sales/index', [
            'sales' => $sales,
            'stats' => $stats,
            'pointsDeVente' => PointDeVente::where('status', 'Success')
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'point' => $pointId,
                'payment_method' => $paymentMethod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * 📝 Page création
     */
    public function create(): Response
    {
        return Inertia::render('admin/sales/create', [
            'products' => Product::with('categories')
                ->where('status', 'Success')
                ->orderBy('name')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'image' => $this->mediaUrl($p->img_1),
                    'category' => $p->categories?->name,
                ]),
            'pointsDeVente' => PointDeVente::where('status', 'Success')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * 💾 Enregistrer la vente (avec date personnalisable)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_date' => 'nullable|date',
            'id_point_de_vente' => 'nullable|exists:points_de_vente,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'payment_method' => 'required|in:cash,mobile_money,card',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id_product' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discount = (float) ($validated['discount'] ?? 0);
            $total = $subtotal - $discount;

            $sale = Sale::create([
                'ref' => 'SALE_' . strtoupper(Str::random(10)),
                'sale_date' => $validated['sale_date'] ?? Carbon::now(),
                'id_user' => Auth::id(),
                'id_point_de_vente' => $validated['id_point_de_vente'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'status' => 'Success',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['id_product']);
                SaleItem::create([
                    'id_sale' => $sale->id,
                    'id_product' => $item['id_product'],
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.sales.show', $sale->id)
                ->with('success', 'Vente enregistrée.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    /**
     * 👁️ Détail vente
     */
    public function show(Sale $sale): Response
    {
        $sale->load(['user', 'pointDeVente', 'items.product']);

        return Inertia::render('admin/sales/show', [
            'sale' => [
                'id' => $sale->id,
                'ref' => $sale->ref,
                'sale_date' => $sale->sale_date->format('d/m/Y H:i'),
                'sale_date_human' => $sale->sale_date->diffForHumans(),
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'total' => $sale->total,
                'payment_method' => $sale->payment_method,
                'status' => $sale->status,
                'notes' => $sale->notes,
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'vendor' => $sale->user ? [
                    'id' => $sale->user->id,
                    'name' => $sale->user->name,
                ] : null,
                'point_de_vente' => $sale->pointDeVente ? [
                    'id' => $sale->pointDeVente->id,
                    'name' => $sale->pointDeVente->name,
                    'address' => $sale->pointDeVente->address,
                ] : null,
                'items' => $sale->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'subtotal' => $i->subtotal,
                    'product_image' => $this->mediaUrl($i->product->img_1),
                ]),
            ],
        ]);
    }

    public function updateStatus(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:Success,pending,failed,waiting,deleted',
        ]);

        $sale->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        $sale->items()->delete();
        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'Vente supprimée.');
    }

    /* ============ Helpers Stats ============ */

    private function getSalesChart(): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $sales = Sale::whereDate('sale_date', $date)
                ->where('status', 'Success')
                ->get();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'ca' => (float) $sales->sum('total'),
                'count' => $sales->count(),
            ];
        }
        return $data;
    }

    private function getTopProducts(): array
    {
        return SaleItem::select(
                'id_product',
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_ca'),
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
                    'image' => $this->mediaUrl($product->img_1),
                    'qty' => (int) $i->total_qty,
                    'ca' => (float) $i->total_ca,
                ];
            })
            ->toArray();
    }

    private function getTopPointsDeVente(): array
    {
        return Sale::select(
                'id_point_de_vente',
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(total) as total_ca'),
            )
            ->where('status', 'Success')
            ->whereNotNull('id_point_de_vente')
            ->groupBy('id_point_de_vente')
            ->orderByDesc('total_ca')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                $point = PointDeVente::find($s->id_point_de_vente);
                return [
                    'id' => $s->id_point_de_vente,
                    'name' => $point?->name ?? 'Inconnu',
                    'sales' => (int) $s->sales_count,
                    'ca' => (float) $s->total_ca,
                ];
            })
            ->toArray();
    }

    private function getTopCategories(): array
    {
        return DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.id_product')
            ->join('categories', 'categories.id', '=', 'products.id_category')
            ->join('sales', 'sales.id', '=', 'sale_items.id_sale')
            ->where('sales.status', 'Success')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_ca'),
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_ca')
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'qty' => (int) $c->total_qty,
                'ca' => (float) $c->total_ca,
            ])
            ->toArray();
    }

    private function getPaymentMethodsBreakdown(): array
    {
        $stats = Sale::where('status', 'Success')
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $totalCA = $stats->sum('total') ?: 1;

        $methods = [
            'cash' => ['name' => 'Espèces', 'icon' => 'banknote'],
            'mobile_money' => ['name' => 'Mobile Money', 'icon' => 'smartphone'],
            'card' => ['name' => 'Carte bancaire', 'icon' => 'credit-card'],
        ];

        $result = [];
        foreach ($methods as $key => $cfg) {
            $stat = $stats->get($key);
            $total = $stat ? (float) $stat->total : 0;
            $result[] = [
                'key' => $key,
                'name' => $cfg['name'],
                'icon' => $cfg['icon'],
                'count' => $stat ? (int) $stat->count : 0,
                'total' => $total,
                'percent' => round(($total / $totalCA) * 100, 1),
            ];
        }

        return $result;
    }

    private function getRecentSales(): array
    {
        return Sale::with(['user', 'pointDeVente'])
            ->where('status', 'Success')
            ->orderByDesc('sale_date')
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'ref' => $s->ref,
                    'total' => (float) $s->total,
                    'customer_name' => $s->customer_name,
                    'point_name' => $s->pointDeVente?->name,
                    'vendor_name' => $s->user?->name,
                    'sale_date_human' => $s->sale_date->diffForHumans(),
                ];
            })
            ->toArray();
    }

    private function formatSaleListItem(Sale $s): array
    {
        return [
            'id' => $s->id,
            'ref' => $s->ref,
            'sale_date' => $s->sale_date->format('d/m/Y H:i'),
            'sale_date_human' => $s->sale_date->diffForHumans(),
            'customer_name' => $s->customer_name,
            'customer_phone' => $s->customer_phone,
            'total' => (float) $s->total,
            'payment_method' => $s->payment_method,
            'status' => $s->status,
            'items_count' => $s->items->count(),
            'point_name' => $s->pointDeVente?->name,
            'vendor_name' => $s->user?->name,
        ];
    }
}