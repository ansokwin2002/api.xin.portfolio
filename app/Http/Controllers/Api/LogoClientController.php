<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogoClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LogoClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = LogoClient::latest()->get()->map(function ($client) {
            $client->image = asset('storage/' . $client->image);
            return $client;
        });

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePath = $request->file('image')->store('clients', 'public');

        $client = LogoClient::create([
            'image' => $imagePath,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logo Client created successfully',
            'data' => $client
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $client = LogoClient::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Logo Client not found'
            ], 404);
        }

        $client->image = asset('storage/' . $client->image);

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $client = LogoClient::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Logo Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['title', 'subtitle']);

        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete($client->image);
            $data['image'] = $request->file('image')->store('clients', 'public');
        }

        $client->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Logo Client updated successfully',
            'data' => $client
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $client = LogoClient::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Logo Client not found'
            ], 404);
        }

        // Delete image
        Storage::disk('public')->delete($client->image);
        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logo Client deleted successfully'
        ]);
    }
}
