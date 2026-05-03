<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = InventoryItem::with('category:id,name');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return Inertia::render('admin/Inventory/Index', [
            'items' => $query->orderBy('name')->paginate(20),
            'categories' => ItemCategory::withCount('items')->orderBy('name')->get(),
            'lowStockCount' => InventoryItem::whereColumn('quantity', '<=', 'min_stock')->count(),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit' => 'required|string|max:20',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        InventoryItem::create($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Item added.']);
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $inventoryItem->update($validated);

        return back()->with('flash', ['type' => 'success', 'message' => 'Item updated.']);
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Item deleted.']);
    }

    // --- Stock Movements ---
    public function addStock(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($inventoryItem, $validated, $request) {
            StockMovement::create([
                'item_id' => $inventoryItem->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reference' => $validated['reference'] ?? null,
                'performed_by' => $request->user()->id,
                'performed_at' => now(),
            ]);

            if ($validated['type'] === 'in') {
                $inventoryItem->increment('quantity', $validated['quantity']);
            } elseif ($validated['type'] === 'out') {
                $inventoryItem->decrement('quantity', min($validated['quantity'], $inventoryItem->quantity));
            } else {
                $inventoryItem->update(['quantity' => $validated['quantity']]);
            }
        });

        return back()->with('flash', ['type' => 'success', 'message' => 'Stock updated.']);
    }

    // --- Categories ---
    public function storeCategory(Request $request): RedirectResponse
    {
        ItemCategory::create($request->validate(['name' => 'required|string|max:100|unique:item_categories,name']));

        return back()->with('flash', ['type' => 'success', 'message' => 'Category created.']);
    }

    public function destroyCategory(ItemCategory $itemCategory): RedirectResponse
    {
        $itemCategory->delete();

        return back()->with('flash', ['type' => 'success', 'message' => 'Category deleted.']);
    }
}
