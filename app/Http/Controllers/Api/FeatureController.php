<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Http\Resources\FeatureResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeatureController extends Controller
{
    use ApiResponse;
    
    public function index()
    {
        $features = Feature::withoutTrashed()->latest()->get();

        return $this->successResponse(
            FeatureResource::collection($features), 
            'Features fetched successfully'
        );
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'memorial_text' => 'required|string',
                'memorial_date' => 'required|date',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Validation failed',
                    $validator->errors(),
                    422
                );
            }

            $validated = $validator->validated();

            $path = $request->file('image')->store('uploads', 'public');

            $feature = new Feature();
            $feature->image_url = $path;
            $feature->memorial_text = $validated['memorial_text'];
            $feature->memorial_date = $validated['memorial_date'];
            $feature->save();

            return $this->successResponse(
                new FeatureResource($feature),
                'Feature created successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse();
        }
    }

    public function show(Feature $feature)
    {
        return $this->successResponse(
            new FeatureResource($feature), 
            'Feature fetched successfully'
        );
    }

    public function update(Request $request, Feature $feature)
    {
        try {
            $validator = Validator::make($request->all(), [
                'memorial_text' => 'sometimes|required|string',
                'memorial_date' => 'sometimes|required|date',
                'image' => 'sometimes|required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Validation failed',
                    $validator->errors(),
                    422
                );
            }

            $validated = $validator->validated();

            if ($request->hasFile('image')) {
                $validated['image_url'] = $request->file('image')->store('uploads', 'public');
                unset($validated['image']);
            }

            $feature->update($validated);

            return $this->successResponse(
                new FeatureResource($feature),
                'Feature updated successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse();
        }
    }

    public function trashed()
    {
        $features = Feature::onlyTrashed()->latest()->get();

        return $this->successResponse(
            FeatureResource::collection($features), 
            'Trashed features fetched successfully'
        );
    }

    public function restore(Feature $feature)
    {
        abort_unless($feature->trashed(), 404);

        $feature->restore();

        return $this->successResponse(
            new FeatureResource($feature), 
            'Feature restored successfully'
        );
    }

    public function softDelete(Feature $feature)
    {
        $feature->delete();
        
        return $this->successResponse(
            null, 
            'Feature deleted successfully'
        );
    }

    public function permanentlyDelete(Feature $feature)
    {
        abort_unless($feature->trashed(), 404);

        $feature->forceDelete();

        return $this->successResponse(
            null, 
            'Feature deleted permanently'
        );
    }
}
