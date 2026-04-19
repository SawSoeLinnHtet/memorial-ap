<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Feature;
use App\Http\Resources\CollectionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $collections = Collection::with('features')->withoutTrashed()->latest()->get();

        return $this->successResponse(
            CollectionResource::collection($collections), 
            'Collections fetched successfully'
        );
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                'memorial_date' => 'required|date',
                'images' => 'required|array|min:1',
                'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Validation failed',
                    $validator->errors(),
                    422
                );
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $collection = new Collection();
            $collection->name = $validated['name'];
            $collection->description = $validated['description'];
            $collection->date = $validated['memorial_date'];
            $collection->save();

            $featureIds = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads', 'public');

                $feature = new Feature();
                $feature->image_url = $path;
                $feature->memorial_text = $validated['description'];
                $feature->memorial_date = $validated['memorial_date'];
                $feature->save();

                $featureIds[] = $feature->id;
            }

            $collection->features()->attach($featureIds);

            DB::commit();

            $collection->load('features');

            return $this->successResponse(
                new CollectionResource($collection),
                'Collection created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Something went wrong',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function show(Collection $collection)
    {
        $collection->load('features');
        
        return $this->successResponse(
            new CollectionResource($collection), 
            'Collection fetched successfully'
        );
    }

    public function update(Request $request, Collection $collection)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                'memorial_date' => 'required|date',
                'images' => 'required|array|min:1',
                'images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Validation failed',
                    $validator->errors(),
                    422
                );
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            $collection->name = $validated['name'];
            $collection->description = $validated['description'];
            $collection->date = $validated['memorial_date'];
            $collection->save();

            $featureIds = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads', 'public');

                $feature = new Feature();
                $feature->image_url = $path;
                $feature->memorial_text = $validated['description'];
                $feature->memorial_date = $validated['memorial_date'];
                $feature->save();

                $featureIds[] = $feature->id;
            }

            $collection->features()->sync($featureIds);

            DB::commit();

            $collection->load('features');

            return $this->successResponse(
                new CollectionResource($collection),
                'Collection updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Something went wrong',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function softDelete(Collection $collection)
    {
        try {
            DB::beginTransaction();

            $collection->delete();

            DB::commit();

            return $this->successResponse(
                null,
                'Collection deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Something went wrong',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function restore(Collection $collection)
    {
        abort_unless($collection->trashed(), 404);

        try {
            DB::beginTransaction();

            $collection->restore();

            DB::commit();

            return $this->successResponse(
                new CollectionResource($collection->load('features')),
                'Collection restored successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Something went wrong',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function permanentlyDelete(Collection $collection)
    {
        abort_unless($collection->trashed(), 404);

        try {
            DB::beginTransaction();

            $collection->forceDelete();

            DB::commit();

            return $this->successResponse(
                null,
                'Collection deleted permanently'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse(
                'Something went wrong',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function trashed()
    {
        $collections = Collection::onlyTrashed()->latest()->get();

        return $this->successResponse(
            CollectionResource::collection($collections),
            'Trashed collections fetched successfully'
        );
    }
}
