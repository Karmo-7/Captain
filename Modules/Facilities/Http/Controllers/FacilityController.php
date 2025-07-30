<?php

namespace Modules\Facilities\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Facilities\Entities\Facility;
use Modules\Facilities\Http\Requests\FacilityRequest;
use Modules\Facilities\Http\Requests\FacilityUpdateRequest;
use Modules\Stadium\Entities\Stadium;
use App\Traits\ImageDeletable;
use App\Traits\ImageUploadable;



class FacilityController extends Controller
{
    use ImageDeletable, ImageUploadable;


    public function create(FacilityRequest $request)
    {
        $stadium_id = $request->stadium_id;
        $stadium = Stadium::find($stadium_id);
        if (!$stadium) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Stadium not found.'
            ], 404);
        }
        if (auth()->id() !== $stadium->user_id ) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'You are not the owner of this stadium.'
            ], 403);
        }
        $validated = $request->validated();
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $photoPaths = $this->uploadImages($request->file('photos'), 'Facilities');
        }

        $validated['photos'] = $photoPaths;

        $facility = Facility::create($validated);
        $facility->load('stadium');

        return response()->json([
            'status' => true,
            'status_code' => 201,
            'message' => 'Facility added successfully.',
            'data' => [
                'Facility' => $facility
            ]
        ], 201);
    }

    public function update(FacilityUpdateRequest $request ,$id){
        $facility=Facility::with('stadium')->find($id);
        if (!$facility) {
            return response()->json([
                'status'=>false,
                'status_code'=>404,
                'message'=>'Facility not found to update it.'
            ]);
        }
        if(auth()->id() !== $facility->stadium->user_id){
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to update this Facility',
            ], 401);
        }
        $validate=$request->validated();
        $facility->update($validate);
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Facility updated successfully',
            'data' => [
                'Facility' => $facility
            ]
        ], 200);
    }

    public function view($id){
        $facility=Facility::with('stadium')->find($id);
        if(!$facility){
            return response()->json([
                'status'=>false,
                'status_code'=>404,
                'message'=>'Facility not Found to show it'
            ]);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Facility retrieved successfully',
            'data' => [
                'Facility' => $facility
            ]
        ], 200);

    }

    public function viewall($stadium_id){
        $stadium=Stadium::find($stadium_id);
        if(!$stadium){
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'stadium not Found to show thier facilities'
            ]);
        }
        $facilities=Facility::with('stadium')->where('stadium_id',$stadium_id)->get();
        return response()->json([
        'status'=>true,
        'status_code'=>200,
        'message'=>'Facilities of stadium',
        'data'=>[
            'Facilities'=>$facilities
        ]
        ],200);
    }

    public function delete($id)
    {
        $facility = Facility::with('stadium')->find($id);
        if (!$facility) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Facility not Found to delete it'
            ]);
        }
        if (auth()->id() !== $facility->stadium->user_id ) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to delete this Facility',
            ], 401);
        }
        $this->deleteImages($facility->photos ?? []);
        $facility->delete();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Facility deleted successfully',
        ]);
    }

}
