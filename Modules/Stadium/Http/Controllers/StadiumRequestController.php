<?php

namespace Modules\Stadium\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumRequest;
use Modules\Stadium\Http\Requests\StadiumRequestForm;
use Illuminate\Support\Facades\Storage;
use Modules\Stadium\Http\Requests\StadiumRequestUpdateForm;
use App\Traits\ImageDeletable;
use App\Traits\ImageUploadable;


class StadiumRequestController extends Controller
{
    use ImageDeletable, ImageUploadable;


    public function AddRequest(StadiumRequestForm $request)
    {
        $user_id = auth()->id();
        $validated = $request->validated();
        $validated['user_id'] = $user_id;

        // تحقق من وجود طلب سابق غير مرفوض بنفس التفاصيل
        $existingRequest = StadiumRequest::where('user_id', $user_id)
            ->where('name', $validated['name'])
            ->where('location', $validated['location'])
            ->where('sport_id', $validated['sport_id'])
            ->where('status', '!=', 'rejected')
            ->first();

        // تحقق من وجود ملعب بنفس التفاصيل
        $existingStadium = Stadium::where('name', $validated['name'])
            ->where('location', $validated['location'])
            ->where('sport_id', $validated['sport_id'])
            ->first();

        // رفض الطلب إذا أحد الشرطين تحقق
        if ($existingRequest || $existingStadium) {
            return response()->json([
                'status' => false,
                'status_code' => 409,
                'message' => 'A similar stadium already exists or you have an active request with the same information.',
                'existing_request' => $existingRequest,
                'existing_stadium' => $existingStadium,
            ], 409);
        }

        // رفع الصور وتخزين المسارات
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $photoPaths = $this->uploadImages($request->file('photos'), 'stadiums');
        }

        $validated['photos'] = $photoPaths;
        $ask = StadiumRequest::create($validated);

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Request added successfully.',
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
                'message' => 'Request not found to update it',
            ], 404);
        }

        $validated = $request->validated();
        $newStatus = $validated['status'] ?? null;

        if (isset($validated['status']) && $validated['status'] === 'rejected' && is_array($ask->photos)) {
            foreach ($ask->photos as $photo) {
                $relativePath = str_replace('storage/', '', $photo);
                Storage::disk('public')->delete($relativePath);
            }
            $ask->photos = null;
        }
        // حالة الرفض: حذف الصور أولًا
        $this->deleteImages($ask->photos ?? []);


        $ask->update($validated);

        // حالة القبول: إنشاء ملعب بعد تحديث الطلب
        if ($newStatus === 'approved') {
            $alreadyExists = Stadium::where('name', $ask->name)
                ->where('location', $ask->location)
                ->where('user_id', $ask->user_id)
                ->exists();

            if (!$alreadyExists) {
                Stadium::create([
                    'user_id' => $ask->user_id,
                    'sport_id' => $ask->sport_id,
                    'name' => $ask->name,
                    'location' => $ask->location,
                    'description' => $ask->description,
                    'photos' => $ask->photos,
                    'Length' => $ask->Length,
                    'Width' => $ask->Width,
                    'owner_number' => $ask->owner_number,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Request updated successfully',
            'data' => [
                'Ask' => $ask,
            ]
        ], 200);
    }

    public function deleteRequest($id)
    {
        $ask = StadiumRequest::find($id);

        if (!$ask) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Ask not Found to delete it',
            ], 404);
        }

        if (auth()->id() !== $ask->user_id && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'status_code' => 401,
                'message' => 'Unauthorized to delete this Ask',
            ], 401);
        }

        $this->deleteImages($ask->photos ?? []);

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
