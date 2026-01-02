<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Validation\Rule;

class ExpertCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpertCategory::query();

        if ($request->filled('searchValue')) {
            $query->where('name', 'like', "%{$request->searchValue}%");
        }

        if ($request->filled('filter_category')) {
            $query->where('id', $request->filter_category);
        }

        $categories = $query->latest()->paginate(10);
        $allCategories = ExpertCategory::select('id', 'name')->get();
        $inactiveCategories = ExpertCategory::where('is_active', false)
        ->get();
        $totalCategories = ExpertCategory::all();

        return view('admin-views.expert-category.index', compact('categories', 'allCategories', 'totalCategories', 'inactiveCategories'));
    }

    public function create()
    {

        return view('admin-views.expert-category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expert_categories')->whereNull('deleted_at')
            ],
            'icon' => 'nullable|image|mimes:jpg,png,svg,webp|max:2048',
            'primary_specialty' => 'required|string',
            'description' => 'nullable|string',
            'free_follow_up_duration' => 'nullable|integer',
            'is_refundable' => 'required|boolean',
            'monthly_subscription_fee' => 'required|numeric|min:0',
            'expert_fee' => 'required|numeric|min:0',
            'joining_fee' => 'required|numeric|min:0',
            'cms_heading' => 'required|string',
            'cms_description' => 'nullable|string',
            'card_description' => 'nullable|string',
            'cms_image' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'card_image' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'is_active' => 'required|boolean',
            'sub_categorys' => 'nullable|string',
            'expert_basic' => 'required|numeric|min:0',
            'expert_premium' => 'required|numeric|min:0',
        ]);

        $data = $request->except(['icon', 'cms_image', 'sub_categorys', 'card_image']);
        $subcategories = $request->filled('sub_categorys')
            ? array_filter(array_map('trim', explode(',', $request->sub_categorys)))
            : [];

        $data['sub_categorys'] = !empty($subcategories) ? $subcategories : null;
        if ($request->hasFile('icon')) {
            $filename = uniqid('icon_') . '.' . $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->storeAs('expert-categories', $filename, 'public');
            $data['icon'] = $filename;
        }

        if ($request->hasFile('cms_image')) {
            $cmsFilename = uniqid('cms_') . '.' . $request->file('cms_image')->getClientOriginalExtension();
            $request->file('cms_image')->storeAs('expert-categories', $cmsFilename, 'public');
            $data['cms_image'] = $cmsFilename;
        }
        if ($request->hasFile('card_image')) {
            $cardFilename = uniqid('card_') . '.' . $request->file('card_image')->getClientOriginalExtension();
            $request->file('card_image')->storeAs('expert-categories', $cardFilename, 'public');
            $data['card_image'] = $cardFilename;
        }

        ExpertCategory::create($data);

        Toastr::success(translate('Expert category created successfully'));
        return redirect()->route('admin.expert-category.index');
    }

    public function edit($id)
    {
        $category = ExpertCategory::findOrFail($id);
        return view('admin-views.expert-category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = ExpertCategory::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expert_categories')->ignore($id)->whereNull('deleted_at')
            ],
            'icon' => 'nullable|image|mimes:jpg,png,svg,webp|max:2048',
            'primary_specialty' => 'required|string',
            'description' => 'nullable|string',
            'free_follow_up_duration' => 'nullable|integer',
            'is_refundable' => 'required|boolean',
            'monthly_subscription_fee' => 'required|numeric|min:0',
            'expert_fee' => 'required|numeric|min:0',
            'joining_fee' => 'required|numeric|min:0',
            'cms_heading' => 'required|string',
            'cms_description' => 'nullable|string',
            'cms_image' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'is_active' => 'required|boolean',
            'sub_categorys' => 'nullable|string',
            'card_image' => 'nullable|image|mimes:jpg,png,webp|max:2048',
            'card_description' => 'nullable|string',
            'expert_basic' => 'required|numeric|min:0',
    'expert_premium' => 'required|numeric|min:0',
        ]);

        $data = $request->except(['icon', 'cms_image', 'sub_categorys', 'card_image']);
        $subcategories = $request->filled('sub_categorys')
            ? array_filter(array_map('trim', explode(',', $request->sub_categorys)))
            : [];

        $data['sub_categorys'] = !empty($subcategories) ? $subcategories : null;
        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete('expert-categories/' . $category->icon);
            }
            $filename = uniqid('icon_') . '.' . $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->storeAs('expert-categories', $filename, 'public');
            $data['icon'] = $filename;
        }

        if ($request->hasFile('cms_image')) {
            if ($category->cms_image) {
                Storage::disk('public')->delete('expert-categories/' . $category->cms_image);
            }
            $cmsFilename = uniqid('cms_') . '.' . $request->file('cms_image')->getClientOriginalExtension();
            $request->file('cms_image')->storeAs('expert-categories/', $cmsFilename, 'public');
            $data['cms_image'] = $cmsFilename;
        }
        if ($request->hasFile('card_image')) {
            if ($category->card_image) {
                Storage::disk('public')->delete('expert-categories/' . $category->card_image);
            }
            $cardFilename = uniqid('card_') . '.' . $request->file('card_image')->getClientOriginalExtension();
            $request->file('card_image')->storeAs('expert-categories/', $cardFilename, 'public');
            $data['card_image'] = $cardFilename;
        }
        $category->update($data);

        Toastr::success(translate('Expert category updated successfully'));
        return redirect()->route('admin.expert-category.index');
    }

    public function destroy($id)
    {
        $category = ExpertCategory::findOrFail($id);
        if ($category->icon) Storage::disk('public')->delete($category->icon);
        if ($category->cms_image) Storage::disk('public')->delete($category->cms_image);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => translate('Expert category deleted successfully'),
            'status' => $category->is_active
        ]);
    }

    public function toggleStatus($id)
    {
        $category = ExpertCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => translate('Status updated'),
            'status' => $category->is_active
        ]);
    }
}
