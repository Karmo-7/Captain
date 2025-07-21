<?php

namespace Modules\Sport\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Sport\Entities\Sport;
use Modules\Sport\Http\Requests\requestsport;
use Illuminate\Routing\Controller;
use Modules\Sport\Http\Requests\updaterequestsport;


class SportController extends Controller
{
    public function create(requestsport $request)
    {
        $validate=$request->validated();

        if ($request->hasFile('photo')) {
            $imagePath = $request->file('photo')->store('modules/sport/images', 'public');
            $validate['photo'] = $imagePath;
        }
        $sport=Sport::create($validate);
        return response()->json([
            'status'=>'true',
            'message' => 'Sport created successfully',
            'data' => $sport,
        ], 201);

    }
    public function update(updaterequestsport $request, $id)
    {
        $sport = Sport::find($id);
        if (!$sport) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Sport not found',
            ], 404);
        }
        $validated = $request->validated();
        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($sport->photo && \Storage::disk('public')->exists($sport->photo)) {
                \Storage::disk('public')->delete($sport->photo);
            }
            // رفع الصورة الجديدة
            $imagePath = $request->file('photo')->store('modules/sport/images', 'public');
            $validated['photo'] = $imagePath;
        }
        $sport->update($validated);
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Sport updated successfully',
            'data' => [
                'sport' => $sport
            ]
        ], 200);
    }

    public function delete($id){
        $sport=Sport::find($id);
        if (!$sport) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Sport not found',
            ], 404);
        }
        if ($sport->photo && \Storage::disk('public')->exists($sport->photo)) {
            \Storage::disk('public')->delete($sport->photo);
        }

        $sport->delete();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Sport deleted successfully',
        ], 200);

    }

    public function view ($id){
        $sport=Sport::find($id);
        if (!$sport){
            return response()->json([
                'status'=>false,
                'status_code' => 404,
                'message' => 'Sport not found',
            ], 404);
        }
        return response()->json([
            'status'=>true,
            'status_code'=>200,
            'data' => [
                'Sport' => $sport
            ]
        ], 200);


    }


    public function viewall()
    {
        $sport=Sport::all();
        
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Sport retrieved successfully',
            'data' => [
                'Sports:' => $sport
            ]
        ], 200);
    }



}
