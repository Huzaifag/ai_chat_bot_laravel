<?php

namespace App\Http\Controllers;

use App\Models\AiApiConfig;
use Illuminate\Http\Request;

class AiApiConfigController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return AiApiConfig::all();
        }

        $configs = AiApiConfig::all();
        return view('admin.ai-api-configs', compact('configs'));
    }

    public function create()
    {
        return view('admin.ai-api-configs');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'api_key'  => 'required|string',
            'version'  => 'nullable|string',
            'is_active'=> 'boolean',
        ]);

        // If this config is set as active, deactivate all others
        if (!empty($validated['is_active'])) {
            AiApiConfig::where('id', '!=', null)->update(['is_active' => false]);
        }

        AiApiConfig::create($validated);

        return redirect()->route('admin.ai-api-configs.index')->with('success', 'API configuration created successfully');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $config = AiApiConfig::findOrFail($id);
        return view('admin.ai-api-configs', compact('config'));
    }

    public function update(Request $request, $id)
    {
        $config = AiApiConfig::findOrFail($id);

        $validated = $request->validate([
            'provider' => 'required|string',
            'api_key'  => 'nullable|string',
            'version'  => 'nullable|string',
            'is_active'=> 'boolean',
        ]);

        // If this config is set as active, deactivate all others
        if (!empty($validated['is_active'])) {
            AiApiConfig::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $config->update($validated);

        return redirect()->route('admin.ai-api-configs.index')->with('success', 'API configuration updated successfully');
    }

    public function destroy($id)
    {
        AiApiConfig::findOrFail($id)->delete();

        return redirect()->route('admin.ai-api-configs.index')->with('success', 'API configuration deleted successfully');
    }
}
