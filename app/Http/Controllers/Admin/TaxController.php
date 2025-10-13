<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TaxController extends Controller
{
    public function index()
    {
        $taxes = Tax::orderBy('is_default', 'desc')->orderBy('name')->get();
        return view('admin.taxes.index', compact('taxes'));
    }

    public function create()
    {
        $tax = new Tax();
        return view('admin.taxes.create', compact('tax'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:taxes,name'],
            'percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'   => ['nullable', 'boolean'],
            'is_default'  => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active']  = (bool)($data['is_active']  ?? false);
        $data['is_default'] = (bool)($data['is_default'] ?? false);

        DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                Tax::where('is_default', true)->update(['is_default' => false]);
            }
            Tax::create($data);
        });

        return redirect()->route('admin.taxes.index')->with('success', 'Tax created successfully.');
    }

    public function edit(Tax $tax)
    {
        return view('admin.taxes.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('taxes', 'name')->ignore($tax->id)],
            'percent'     => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'   => ['nullable', 'boolean'],
            'is_default'  => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active']  = (bool)($data['is_active']  ?? false);
        $data['is_default'] = (bool)($data['is_default'] ?? false);

        DB::transaction(function () use ($tax, $data) {
            if ($data['is_default']) {
                Tax::where('id', '!=', $tax->id)->where('is_default', true)->update(['is_default' => false]);
            }
            $tax->update($data);
        });

        return redirect()->route('admin.taxes.index')->with('success', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax)
    {
        if ($tax->is_default) {
            return back()->with('error', 'Default tax cannot be deleted. Make another tax default first.');
        }

        $tax->delete();
        return redirect()->route('admin.taxes.index')->with('success', 'Tax deleted successfully.');
    }

    // Optional quick actions below

    public function toggleActive(Tax $tax)
    {
        $tax->update(['is_active' => ! $tax->is_active]);
        return back()->with('success', 'Tax status updated.');
    }

    public function makeDefault(Tax $tax)
    {
        DB::transaction(function () use ($tax) {
            Tax::where('is_default', true)->update(['is_default' => false]);
            $tax->update(['is_default' => true, 'is_active' => true]);
        });

        return back()->with('success', 'Default tax updated.');
    }
}
