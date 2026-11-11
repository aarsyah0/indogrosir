<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        return view('master.categories.index', [
            'categories' => ItemCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:item_categories,code'],
            'name' => ['required', 'string', 'max:150', 'unique:item_categories,name'],
        ]);

        $category = ItemCategory::query()->create($data + ['is_active' => true]);
        $this->audit->log($request->user(), 'item_category.create', $category, null, $category->toArray());

        return redirect()->route('master.categories.index')->with('success', 'Kategori barang ditambahkan.');
    }
}
