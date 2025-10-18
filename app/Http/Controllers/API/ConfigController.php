<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\Validator;
use App\Traits\LogsActivity;

class ConfigController extends Controller
{
    use LogsActivity;
    /**
     * Get all configurations
     */
    public function index()
    {
        try {
            $configs = Config::orderBy('name')->get();
            return response()->json([
                'success' => true,
                'data' => $configs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific configuration by name
     */
    public function show($name)
    {
        try {
            $config = Config::where('name', $name)->first();

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update configuration
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'value' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = Config::setValue($request->name, $request->value);

            // Log the activity
            $this->logActivity('Configuration Management', "Created/updated configuration: {$request->name}");

            return response()->json([
                'success' => true,
                'message' => 'Configuration saved successfully',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update configuration
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:configs,id',
                'name' => 'required|string|max:255|unique:configs,name,' . $request->id,
                'value' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = Config::findOrFail($request->id);
            $oldName = $config->name;
            $config->update($request->only(['name', 'value']));

            // Log the activity
            $this->logActivity('Configuration Management', "Updated configuration: {$oldName} → {$config->name}");

            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete configuration
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:configs,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = Config::findOrFail($request->id);

            // Log the activity before deletion
            $this->logActivity('Configuration Management', "Deleted configuration: {$config->name}");

            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
