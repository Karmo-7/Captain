<?php

namespace Modules\Stadium\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Stadium\Entities\StadiumSlot;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Stadium\Entities\Stadium;

class StadiumSlotController extends Controller
{
    // ✅ دالة للرد الناجح
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    // ✅ دالة للرد بالفشل
    protected function errorResponse($message = 'Something went wrong', $code = 400, $data = null)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function index()
    {
        return $this->successResponse(StadiumSlot::all(), 'Stadium slots retrieved successfully');
    }

    public function store(Request $request)
{
    $data = $request->all();

    try {
        if (isset($data[0])) {
            // Batch insert
            $inserted = [];

            foreach ($data as $item) {
                Validator::make($item, [
                    'start_time' => 'required|string|max:45',
                    'end_time' => 'required|string|max:45',
                    'stadium_id' => 'required|integer|exists:stadiums,id',
                    'status' => 'nullable|in:available,booked,maintenance',
                ])->validate();

                // ✅ تحقق من وقت الملعب
                $stadium = \Modules\Stadium\Entities\Stadium::find($item['stadium_id']);
                if (!$stadium) {
                    return $this->errorResponse("الملعب غير موجود", 404);
                }

                $slotStart = \Carbon\Carbon::parse($item['start_time']);
                $slotEnd = \Carbon\Carbon::parse($item['end_time']);
                $stadiumStart = \Carbon\Carbon::parse($stadium->start_time);
                $stadiumEnd = \Carbon\Carbon::parse($stadium->end_time);

                if ($slotStart->lt($stadiumStart) || $slotEnd->gt($stadiumEnd)) {
                    return $this->errorResponse(
                        "وقت الحجز يجب أن يكون ضمن وقت الملعب: {$stadiumStart->format('H:i')} - {$stadiumEnd->format('H:i')}",
                        422
                    );
                }

                // ✅ تحقق من التكرار
                $exists = StadiumSlot::where('stadium_id', $item['stadium_id'])
                    ->where('start_time', $item['start_time'])
                    ->where('end_time', $item['end_time'])
                    ->exists();

                if ($exists) {
                    return $this->errorResponse(
                        "Slot already exists for stadium at {$item['start_time']} - {$item['end_time']}",
                        409
                    );
                }

                $inserted[] = StadiumSlot::create($item);
            }

            return $this->successResponse($inserted, 'Slots created successfully', 201);
        } else {
            // Single insert
            $validated = $request->validate([
                'start_time' => 'required|string|max:45',
                'end_time' => 'required|string|max:45',
                'stadium_id' => 'required|integer|exists:stadiums,id',
                'status' => 'nullable|in:available,booked,maintenance',
            ]);

            // ✅ تحقق من وقت الملعب
            $stadium = \Modules\Stadium\Entities\Stadium::find($validated['stadium_id']);
            if (!$stadium) {
                return $this->errorResponse("الملعب غير موجود", 404);
            }

            $slotStart = \Carbon\Carbon::parse($validated['start_time']);
            $slotEnd = \Carbon\Carbon::parse($validated['end_time']);
            $stadiumStart = \Carbon\Carbon::parse($stadium->start_time);
            $stadiumEnd = \Carbon\Carbon::parse($stadium->end_time);

            if ($slotStart->lt($stadiumStart) || $slotEnd->gt($stadiumEnd)) {
                return $this->errorResponse(
                    "وقت الحجز يجب أن يكون ضمن وقت الملعب: {$stadiumStart->format('H:i')} - {$stadiumEnd->format('H:i')}",
                    422
                );
            }

            // ✅ تحقق من التكرار
            $exists = StadiumSlot::where('stadium_id', $validated['stadium_id'])
                ->where('start_time', $validated['start_time'])
                ->where('end_time', $validated['end_time'])
                ->exists();

            if ($exists) {
                return $this->errorResponse(
                    'Slot already exists for this stadium at this time',
                    409
                );
            }

            $slot = StadiumSlot::create($validated);

            return $this->successResponse($slot, 'Slot created successfully', 201);
        }
    } catch (ValidationException $e) {
        return $this->errorResponse('Validation failed', 422, $e->errors());
    } catch (\Exception $e) {
        return $this->errorResponse('Server error', 500, $e->getMessage());
    }
}

    public function show($id)
    {
        $slot = StadiumSlot::find($id);

        if (!$slot) {
            return $this->errorResponse('Slot not found', 404);
        }

        return $this->successResponse($slot, 'Slot retrieved successfully');
    }

   public function update(Request $request, $id)
{
    $slot = StadiumSlot::find($id);

    if (!$slot) {
        return $this->errorResponse('Slot not found', 404);
    }

    try {
        $validated = $request->validate([
            'start_time' => 'sometimes|string|max:45',
            'end_time' => 'sometimes|string|max:45',
            'stadium_id' => 'sometimes|integer|exists:stadiums,id',
            'status' => 'nullable|in:available,booked,maintenance',
        ]);

        // ✅ تحديد stadium_id إما من البيانات الجديدة أو القديمة
        $stadiumId = $validated['stadium_id'] ?? $slot->stadium_id;
        $stadium = \Modules\Stadium\Entities\Stadium::find($stadiumId);

        if (!$stadium) {
            return $this->errorResponse("الملعب غير موجود", 404);
        }

        // ✅ التحقق من وقت الملعب
        $newStart = $validated['start_time'] ?? $slot->start_time;
        $newEnd = $validated['end_time'] ?? $slot->end_time;

        $slotStart = \Carbon\Carbon::parse($newStart);
        $slotEnd = \Carbon\Carbon::parse($newEnd);
        $stadiumStart = \Carbon\Carbon::parse($stadium->start_time);
        $stadiumEnd = \Carbon\Carbon::parse($stadium->end_time);

        if ($slotStart->lt($stadiumStart) || $slotEnd->gt($stadiumEnd)) {
            return $this->errorResponse(
                "وقت الحجز يجب أن يكون ضمن وقت الملعب: {$stadiumStart->format('H:i')} - {$stadiumEnd->format('H:i')}",
                422
            );
        }

        // ✅ التحقق من التكرار في حالة تغيير الوقت أو الملعب
        $exists = StadiumSlot::where('stadium_id', $stadiumId)
            ->where('start_time', $newStart)
            ->where('end_time', $newEnd)
            ->where('id', '!=', $id) // حتى لا يقارن نفسه
            ->exists();

        if ($exists) {
            return $this->errorResponse(
                'Slot already exists for this stadium at this time',
                409
            );
        }

        // ✅ تحديث البيانات
        $slot->update($validated);

        return $this->successResponse($slot, 'Slot updated successfully');
    } catch (ValidationException $e) {
        return $this->errorResponse('Validation failed', 422, $e->errors());
    }
}

    public function destroy($id)
    {
        $slot = StadiumSlot::find($id);

        if (!$slot) {
            return $this->errorResponse('Slot not found', 404);
        }

        $slot->delete();

        return $this->successResponse(null, 'Slot deleted successfully');
    }


    public function getSlotsByStadium($stadium_id)
{
    // ✅ تأكد إن الملعب موجود
    $stadium = \Modules\Stadium\Entities\Stadium::find($stadium_id);

    if (!$stadium) {
        return $this->errorResponse("الملعب غير موجود", 404);
    }

    // ✅ رجّع كل الساعات المرتبطة بهذا الملعب
    $slots = StadiumSlot::where('stadium_id', $stadium_id)->get();

    if ($slots->isEmpty()) {
        return $this->errorResponse("لا يوجد أوقات متاحة لهذا الملعب", 404);
    }

    return $this->successResponse($slots, "أوقات الملعب {$stadium->name} تم جلبها بنجاح");
}



public function generateSlots($stadium_id)
{
    $stadium = Stadium::find($stadium_id);

    if (!$stadium) {
        return $this->errorResponse("الملعب غير موجود", 404);
    }

    try {
        $startTime = \Carbon\Carbon::parse($stadium->start_time);
        $endTime = \Carbon\Carbon::parse($stadium->end_time);


        $durationParts = explode(':', $stadium->duration);
        if (count($durationParts) !== 2) {
            return $this->errorResponse("المدة (duration) غير صالحة. يجب أن تكون بصيغة HH:MM", 422);
        }

        $hours = (int) $durationParts[0];
        $minutes = (int) $durationParts[1];
        $durationInMinutes = $hours * 60 + $minutes;

        $slots = [];

        while ($startTime->copy()->addMinutes($durationInMinutes)->lte($endTime)) {
            $slotStart = $startTime->format('H:i');
            $slotEnd = $startTime->copy()->addMinutes($durationInMinutes)->format('H:i');

           
            $exists = StadiumSlot::where('stadium_id', $stadium->id)
                ->where('start_time', $slotStart)
                ->where('end_time', $slotEnd)
                ->exists();

            if (!$exists) {
                $slots[] = StadiumSlot::create([
                    'stadium_id' => $stadium->id,
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'status' => 'available',
                ]);
            }

            $startTime->addMinutes($durationInMinutes);
        }

        if (empty($slots)) {
            return $this->successResponse([], "لا توجد أوقات جديدة تم إنشاؤها، قد تكون جميعها موجودة بالفعل.");
        }

        return $this->successResponse($slots, "تم إنشاء الأوقات بنجاح للملعب {$stadium->name}.");
    } catch (\Exception $e) {
        return $this->errorResponse("حدث خطأ أثناء إنشاء الأوقات", 500, $e->getMessage());
    }
}


}
