<?php

namespace Modules\Stadium\Http\Controllers;

use App\Models\User;
use Google\Service\Analytics\FilterRef;
use Google\Service\Directory\Role;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Http\Requests\filterRequest;
use Modules\Stadium\Http\Requests\StadiumRequestUpdate;
use App\Traits\ImageDeletable;
use App\Traits\ImageUploadable;


class StadiumController extends Controller
{
    use ImageDeletable,ImageUploadable;

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

        // معالجة الصور
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $photoPaths = $this->uploadImages($request->file('photos'), 'stadiums');
        }

        $validate['photos'] = $photoPaths;
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

    public function get_all_owmer(){
        $owners = User::role('stadium_owner', 'web')
            ->with('stadiums')
            ->get();
        return $owners;
    }

    public function nearstadium(Request $request)
    {

        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        $radius = $request->input('radius');
        if(!$userLat||!$userLng){
            return response()->json([
            'status'=>false,
            'message' => 'Latitude and Longitude are required'
            ],400);
        }
        $query = Stadium::select('*')
            ->selectRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance
        ", [$userLat, $userLng, $userLat])
            ->orderBy('distance', 'asc');
        if (!empty($radius) && $radius > 0) {
            $query->having('distance', '<=', $radius);
        }
        $stadiums = $query->get();
        if ($stadiums->isEmpty()) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'You do not have Stadiums in your border.'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Stadiums retrieved successfully',
            'data' => $stadiums
        ]);
    }


    public function filter(filterRequest $request){
        $validate=$request->validated();
        $sportId=$validate['sport_id']??null;
        $minprice=$validate['min_price']??null;
        $maxprice=$validate['max_price']??null;
        $query=Stadium::query()->with('sport');
        if(!empty($sportId)){
            $query->where('sport_id',$sportId);
        }
        if(!empty($minprice)&&!empty($maxprice)){
            $query->whereBetween('price', [$minprice, $maxprice]);
        }
        elseif(!empty($minprice)){
            $query->where('price','>=',$minprice);
        }
        elseif(!empty($maxprice)){
            $query->where('prise','<=',$maxprice);
        }
        $stadiums = $query->get();

        if ($stadiums->isEmpty()) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'No stadiums found with the given filters.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'data' => $stadiums
        ]);
    }


}
