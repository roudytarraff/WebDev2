<?php

namespace App\Http\Controllers\Office;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class OfficeCategoryController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $categories = ServiceCategory::where('office_id', $office->id)->withCount('services')->orderBy('name')->get();
        return view('office.categories.index', compact('office', 'categories'));
    }

    public function create()
    {
        return view('office.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $office = $this->currentOffice();

        $category = new ServiceCategory();
        $category->office_id = $office->id;
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('office.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(string $id)
    {
        $office = $this->currentOffice();
        $category = ServiceCategory::where('office_id', $office->id)->findOrFail($id);
        return view('office.categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $office = $this->currentOffice();
        $category = ServiceCategory::where('office_id', $office->id)->findOrFail($id);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return redirect()->route('office.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(string $id)
    {
        $office = $this->currentOffice();
        $category = ServiceCategory::where('office_id', $office->id)->findOrFail($id);
        $category->delete();

        return redirect()->route('office.categories.index')->with('success', 'Category deleted successfully.');
    }
}
