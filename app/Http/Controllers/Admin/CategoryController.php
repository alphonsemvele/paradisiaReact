<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = Category::query()->withCount('products');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories->getCollection()->transform(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'description' => $cat->description,
                'status' => $cat->status,
                'is_active' => $cat->status !== 'Inactive',
                'products_count' => $cat->products_count,
                'created_at_human' => $cat->created_at->diffForHumans(),
                'created_at_date' => $cat->created_at->format('d/m/Y'),
            ];
        });

        $stats = [
            'total' => Category::count(),
            'active' => Category::where(fn ($q) => $q->where('status', '!=', 'Inactive')->orWhereNull('status'))->count(),
            'inactive' => Category::where('status', 'Inactive')->count(),
            'with_products' => Category::has('products')->count(),
        ];

        return Inertia::render('admin/categories/index', [
            'categories' => $categories,
            'stats' => $stats,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => 'Success',
            'id_user' => Auth::id(),
        ]);

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'status' => $category->status === 'Inactive' ? 'Success' : 'Inactive',
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->withErrors([
                'error' => 'Impossible de supprimer : cette catégorie contient des produits.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}