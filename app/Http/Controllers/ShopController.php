<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Support\PageMeta;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search', '');
        $categoryFilter = $request->query('category', '');
        $priceMin = $request->query('price_min', '');
        $priceMax = $request->query('price_max', '');
        $sortBy = $request->query('sort', 'recent');

        $query = Product::query()
            ->with(['user', 'categories'])
            ->where('status', 'Success');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($categoryFilter) {
            $query->where('id_category', $categoryFilter);
        }

        if ($priceMin !== '') {
            $query->where('price', '>=', $priceMin);
        }

        if ($priceMax !== '') {
            $query->where('price', '<=', $priceMax);
        }

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        $formattedProducts = $products->through(fn ($p) => $this->formatProduct($p));

        $categories = Category::withCount(['products' => function ($q) {
            $q->where('status', 'Success');
        }])
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'products_count' => $c->products_count,
            ]);

        return Inertia::render('dashboard/shop/index', [
            'products' => $formattedProducts,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $categoryFilter,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'sort' => $sortBy,
            ],
            'cart' => session()->get('cart', []),
        ]);
    }

    public function show(int $id): Response
    {
        $product = Product::with(['categories', 'user', 'components.component'])->findOrFail($id);

        $formatted = $this->formatProduct($product);

        // Nomenclature d'un produit composé : "12 × Ananas simple"...
        $formatted['components'] = $product->components
            ->filter(fn ($line) => $line->component)
            ->map(fn ($line) => [
                'name' => $line->component->name,
                'quantity' => $line->quantity,
            ])
            ->values();

        return Inertia::render('dashboard/shop/show', [
            'product' => $formatted,
            'cart' => session()->get('cart', []),
        ])->withViewData([
            'meta' => PageMeta::make(
                title: $formatted['name'].' — '.number_format((float) $formatted['price'], 0, ',', ' ').' FCFA',
                description: $formatted['description'],
                image: $formatted['image'],
                type: 'product',
            ),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = session()->get('cart', []);
        $cartKey = 'product_'.$product->id;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $validated['quantity'];
        } else {
            $cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $this->mediaUrl($product->img_1),
                'quantity' => $validated['quantity'],
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', $product->name.' ajouté au panier');
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$validated['cart_key']])) {
            if ($validated['quantity'] <= 0) {
                unset($cart[$validated['cart_key']]);
            } else {
                $cart[$validated['cart_key']]['quantity'] = $validated['quantity'];
            }
            session()->put('cart', $cart);
        }

        return back();
    }

    public function removeFromCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$validated['cart_key']])) {
            unset($cart[$validated['cart_key']]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Produit retiré du panier');
    }

    public function clearCart(): RedirectResponse
    {
        session()->forget('cart');

        return back()->with('success', 'Panier vidé');
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $this->mediaUrl($product->img_1),
            'images' => collect([
                $product->img_1, $product->img_2 ?? null, $product->img_3 ?? null,
            ])->filter()->map(fn ($img) => $this->mediaUrl($img))->filter()->values(),
            'category' => $product->categories ? [
                'id' => $product->categories->id,
                'name' => $product->categories->name,
            ] : null,
            'created_at' => $product->created_at,
        ];
    }
}