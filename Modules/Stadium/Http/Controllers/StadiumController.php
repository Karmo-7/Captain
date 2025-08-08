<?php

namespace Modules\Stadium\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Http\Requests\StadiumRequestUpdate;
use App\Traits\ImageDeletable;


class StadiumController extends Controller
{
    use ImageDeletable;

    public function update(StadiumRequestUpdate $request, $id){
        $stadium=Stadium::find($id);
        if (!$stadium) {
            return response()->json([
                'status'=>false,
                'status_code'=>404,
                'message'=>'stadium not found to update it.'
            ]);
        }
        if(auth()->id() !== $stadium->user_id){
            return response()->json([
                'status'=>false,
                'status_code'=>401,
                'message' =>'Unauthorized to Show this Ask'
            ],401);
        }
        $validate=$request->validated();
        $stadium->update($validate);
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Stadium updated successfully',
            'data' => [
                'Stadium' => $stadium
            ]
        ], 200);

    }

    public function view($id){
        $stadium=Stadium::with('facility')->find($id);
        if (!$stadium) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Stadium not found to show it.'
            ]);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Stadium retrieved successfully',
            'data' => [
                'Stadium' => $stadium
            ]
        ], 200);

    }

    public function viewall(){
        $stadiums=Stadium::all();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Stadiums retrieved successfully',
            'data' => [
                'Stadiums' => $stadiums
            ]
        ], 200);

    }

    public function delete($id)
    {
        $stadium = Stadium::find($id);
        if (!$stadium) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Stadium not found to delete it.'
            ]);
        }
        if (auth()->id() !== $stadium->user_id && !auth()->user()->hasRole('admin') ) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to Delete this Stadium'
            ], 401);
        }

        $this->deleteImages($stadium->photos ?? []);
        $stadium->delete();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Stadium deleted successfully',
        ]);
    }


}
