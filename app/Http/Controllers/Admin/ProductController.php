<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $categoryId = $request->get('category');
        $status = $request->get('status');

        $query = Product::with(['categories', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('id_category', $categoryId);
        }

        if ($status === 'active') {
            $query->where('status', 'Success');
        } elseif ($status === 'inactive') {
            $query->where(fn ($q) => $q->where('status', '!=', 'Success')->orWhereNull('status'));
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(fn ($p) => $this->formatProduct($p));

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'Success')->count(),
            'inactive' => Product::where(fn ($q) => $q->where('status', '!=', 'Success')->orWhereNull('status'))->count(),
            'this_month' => Product::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        return Inertia::render('admin/products/index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/create', [
            'categories' => Category::where(fn ($q) => $q->where('status', '!=', 'Inactive')->orWhereNull('status'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'id_category' => 'nullable|exists:categories,id',
            'img_1' => 'nullable|image|max:5120',
            'img_2' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'id_category' => $validated['id_category'] ?? null,
            'id_user' => Auth::id(),
            'status' => 'Success',
        ];

        if ($request->hasFile('img_1')) {
            $data['img_1'] = $this->uploadFile($request->file('img_1'));
        }

        if ($request->hasFile('img_2')) {
            $data['img_2'] = $this->uploadFile($request->file('img_2'));
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produit créé.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'id_category' => $product->id_category,
                'status' => $product->status,
                'img_1' => $this->fileUrl($product->img_1),
                'img_2' => $this->fileUrl($product->img_2),
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'id_category' => 'nullable|exists:categories,id',
            'img_1' => 'nullable|image|max:5120',
            'img_2' => 'nullable|image|max:5120',
            'remove_img_1' => 'nullable|boolean',
            'remove_img_2' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'id_category' => $validated['id_category'] ?? null,
        ];

        // Image 1
        if ($request->boolean('remove_img_1') && $product->img_1) {
            $this->deleteFile($product->img_1);
            $data['img_1'] = null;
        }

        if ($request->hasFile('img_1')) {
            if ($product->img_1) {
                $this->deleteFile($product->img_1);
            }
            $data['img_1'] = $this->uploadFile($request->file('img_1'));
        }

        // Image 2
        if ($request->boolean('remove_img_2') && $product->img_2) {
            $this->deleteFile($product->img_2);
            $data['img_2'] = null;
        }

        if ($request->hasFile('img_2')) {
            if ($product->img_2) {
                $this->deleteFile($product->img_2);
            }
            $data['img_2'] = $this->uploadFile($request->file('img_2'));
        }

        $product->update($data);

        return back()->with('success', 'Produit mis à jour.');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update([
            'status' => $product->status === 'Success' ? 'Inactive' : 'Success',
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteFile($product->img_1);
        $this->deleteFile($product->img_2);

        $product->delete();

        return back()->with('success', 'Produit supprimé.');
    }

    /* ============ Helpers ============ */

    private function formatProduct(Product $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => $p->price,
            'price_formatted' => number_format((float) $p->price, 0, ',', ' ') . ' FCFA',
            'status' => $p->status,
            'is_active' => $p->status === 'Success',
            'img_1' => $this->fileUrl($p->img_1),
            'img_2' => $this->fileUrl($p->img_2),
            'category' => $p->categories ? [
                'id' => $p->categories->id,
                'name' => $p->categories->name,
            ] : null,
            'created_at_human' => $p->created_at->diffForHumans(),
            'created_at_date' => $p->created_at->format('d/m/Y'),
        ];
    }

    /**
     * Génère l'URL publique d'un fichier stocké.
     * Gère les anciens chemins (uploads/...) et les nouveaux (storage disk).
     */
    private function fileUrl(?string $path): ?string
    {
        if (! $path) return null;

        // Déjà une URL complète
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Upload un fichier dans storage/app/public/uploads/products/
     * Retourne le path relatif (ex: "uploads/products/prod_xxx.jpg")
     */
    private function uploadFile($file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'prod_' . uniqid() . '_' . time() . '.' . $extension;

        return $file->storeAs('uploads/products', $filename, 'public');
    }

    /**
     * Supprime un fichier du disk public.
     */
    private function deleteFile(?string $path): void
    {
        if (! $path) return;

        // Si c'est une URL complète, extraire le path relatif
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH) ?? '';
            $path = ltrim(str_replace('/storage/', '', $parsed), '/');
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}