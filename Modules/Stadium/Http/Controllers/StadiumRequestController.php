<?php

namespace Modules\Stadium\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\StadiumRequest;
use Modules\Stadium\Http\Requests\StadiumRequestForm;
use Illuminate\Support\Facades\Storage;
use Modules\Stadium\Http\Requests\StadiumRequestUpdateForm;

class StadiumRequestController extends Controller
{



    public function AddRequest(StadiumRequestForm $request)
    {
        $user_id = auth()->id();
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $existask=StadiumRequest::where('user_id',$user_id)
        ->where('name',$validated['name'])
        ->where('location',$validated['location'])
        ->where('sport_id', $validated['sport_id'])
        ->first();
        if ($existask) {
            return response()->json([
                'status' => false,
                'status_code' => 409,
                'message' => 'You have already submitted a request with the same information.',
                'data' => $existask,
            ], 409);
        }

        $photoPaths = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('stadiums', 'public');
                $photoPaths[] = Storage::url($path);
            }
        }

        $validated['photos'] = $photoPaths;
        $ask = StadiumRequest::create($validated);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Request Added Successfully',
            'data' => $ask
        ]);
    }

    public function ReplyAsk(StadiumRequestUpdateForm $request, $id)
    {
        $ask = StadiumRequest::find($id);

        if (!$ask) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Ask not found to update it',
            ], 404);
        }

        $validated = $request->validated();

        if (isset($validated['status']) && $validated['status'] === 'rejected' && is_array($ask->photos)) {
            foreach ($ask->photos as $photo) {
                $relativePath = str_replace('storage/', '', $photo);
                Storage::disk('public')->delete($relativePath);
            }
            $ask->photos = null;
        }


        $ask->update($validated);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Ask updated successfully',
            'data' => [
                'Ask' => $ask
            ]
        ], 200);
    }

    public function delete($id)
    {
        $ask = StadiumRequest::find($id);

        if (!$ask) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Ask not Found to delete it',
            ], 404);
        }

        if (auth()->id() !== $ask->user_id) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to delete this Ask',
            ], 401);
        }

        if (is_array($ask->photos)) {
            foreach ($ask->photos as $photo) {
                $relativePath = str_replace('/storage/', '', $photo);
                Storage::disk('public')->delete($relativePath);
            }
        }

        $ask->delete();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Ask deleted successfully',
        ], 200);
    }


    public function view($id){
        $ask=StadiumRequest::find($id);
        if (!$ask) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Ask not Found to Show it',
            ], 404);
        }

        if  (auth()->id() !== $ask->user_id && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to Show this Ask',
            ], 401);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Ask:',
            'data' => $ask
        ]);

    }
    public function viewall(){
        $ask = StadiumRequest::all();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Asks retrieved successfully',
            'data' => [
                'Asks' => $ask
            ]
        ], 200);
    }






}
