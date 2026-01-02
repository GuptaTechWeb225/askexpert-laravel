<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CareerCard;
use App\Models\CareerJob;
use App\Models\CareerSection;
use App\Models\CareerBenefits;

class CareerController extends Controller
{

    protected $modelMap = [
        'current_openings' => CareerJob::class,
        'why_join_us' => CareerCard::class,
        'perks' => CareerBenefits::class,
        'hero' => CareerSection::class,
    ];


    public function index($section = 'current_openings')
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'current_openings';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);

        return view('admin-views.content-management.career.index', compact('section', 'items'));
    }

    public function pages($section)
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'current_openings';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);

        return view('admin-views.content-management.career.index', compact('section', 'items', 'data'));
    }

    public function create(Request $request, $section = 'current_openings')
    {
        $section = $request->get('section', 'current_openings'); // default: current_openings

        $viewPath = "admin-views.content-management.career.sections.create." . $section;

        return view($viewPath, compact('section'));
    }

    // Store new data for the selected section
    public function store(Request $request, $section)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];
        $model = new $modelClass;

        $data = $request->except('_token');

        // Handle the 'skills' field from Summernote editor
        if ($request->has('skills')) {
            // Convert the text entered into the editor into a comma-separated array
            $data['skills'] = implode(', ', explode(',', $request->skills));
        }

        if ($request->has('job_description')) {
            $data['description'] = implode(', ', explode(',', $request->job_description));
        }


        // Handle image upload if field exists
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('career', 'public');
        }

        // Fill and save data
        $model->fill($data);
        $model->save();

        // Redirect to the corresponding section page with success message
        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', 'Data added successfully.');
    }


    // Delete a section item based on ID
    public function destroy($section, $id)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        // Validate section
        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.career.pages')
                ->withErrors(['Invalid section']);
        }

        // Find the item by ID and delete it
        $model = $modelMap[$section]::findOrFail($id);
        $model->delete();

        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', 'Item deleted successfully.');
    }

    // Edit an item in the selected section
    public function edit($section, $id)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.career.index')
                ->withErrors(['Invalid section']);
        }

        // Get the model instance for the selected section
        $job = $modelMap[$section]::findOrFail($id);

        if (!$job) {
            return redirect()->route('admin.content-management.career.index')
                ->withErrors(['Invalid data or job not found.']);
        }

        return view("admin-views.content-management.career.sections.edit.$section", compact('job', 'section'));
    }

    // Update the data of a specific item in the selected section
    public function update(Request $request, $section, $id)
    {
        // Map each section to its corresponding model
        $modelMap = [
            'current_openings' => CareerJob::class,
            'why_join_us' => CareerCard::class,
            'perks' => CareerBenefits::class,
            'hero' => CareerSection::class,
        ];

        // Validate if the section exists in the map
        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        // Get the model instance for the selected section
        $model = $modelMap[$section]::findOrFail($id);

        // Validate and update the model data
        $data = $request->except('_token', 'section'); // Remove unnecessary fields

        if ($request->has('skills')) {
            $data['skills'] = $request->skills; // store full HTML
        }

        if ($request->has('job_description')) {
            $data['description'] = $request->job_description; // store full HTML
        }

        if ($request->hasFile('image')) {
            // Handle image update
            $data['image'] = $request->file('image')->store('career', 'public');
        }

        // Fill and save the updated data
        $model->fill($data);
        $model->save();

        // Redirect to the appropriate section page with success message
        return redirect()->route('admin.content-management.career.pages', ['section' => $section])
            ->with('success', 'Data updated successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'section' => 'required|string',
        ]);

        // Section validate karo
        if (!isset($this->modelMap[$request->section])) {
            return response()->json(['message' => 'Invalid section provided.'], 400);
        }

        $modelClass = $this->modelMap[$request->section];

        $item = $modelClass::find($request->id);

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        // is_active toggle karna
        $item->is_active = !$item->is_active;
        $item->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
