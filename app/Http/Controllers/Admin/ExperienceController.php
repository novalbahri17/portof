<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExperienceController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Experiences', [
            'experiences' => Experience::orderBy('type')->orderByDesc('start_date')->get(),
            'employmentTypes' => Experience::EMPLOYMENT_TYPES,
            'workModes' => Experience::WORK_MODES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:work,education',
            'employment_type' => 'nullable|string|in:'.implode(',', array_keys(Experience::EMPLOYMENT_TYPES)),
            'work_mode' => 'nullable|string|in:'.implode(',', array_keys(Experience::WORK_MODES)),
            'title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required_unless:is_current,true|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:5000',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validated['is_current'] ?? false) {
            $validated['end_date'] = null;
        }

        // Tipe pekerjaan & sistem kerja hanya relevan untuk kategori kerja
        if (($validated['type'] ?? 'work') === 'education') {
            $validated['employment_type'] = null;
            $validated['work_mode'] = null;
        }

        Experience::create($validated);

        return back()->with('success', 'Pengalaman berhasil dibuat.');
    }

    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:work,education',
            'employment_type' => 'nullable|string|in:'.implode(',', array_keys(Experience::EMPLOYMENT_TYPES)),
            'work_mode' => 'nullable|string|in:'.implode(',', array_keys(Experience::WORK_MODES)),
            'title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required_unless:is_current,true|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:5000',
            'published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validated['is_current'] ?? false) {
            $validated['end_date'] = null;
        }

        if (($validated['type'] ?? 'work') === 'education') {
            $validated['employment_type'] = null;
            $validated['work_mode'] = null;
        }

        $experience->update($validated);

        return back()->with('success', 'Pengalaman berhasil diperbarui.');
    }

    public function destroy(Experience $experience)
    {
        $experience->delete();

        return back()->with('success', 'Pengalaman berhasil dihapus.');
    }
}
