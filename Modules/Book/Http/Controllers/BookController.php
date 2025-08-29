<?php

namespace Modules\Book\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Book\Entities\Book;
use Modules\Book\Entities\StadiumSlotBooking;
use Modules\Book\Http\Requests\BookRequest;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumSlot;

class BookController extends Controller
{


    public function book(BookRequest $request)
    {

        $user_id = auth()->id();
        $validated = $request->validated();
        $validated['user_id'] = $user_id;

        $slot = StadiumSlot::findOrFail($request->stadium_slot_id);

        //  إذا الـ Slot بالصيانة العامة، ما بيتحجز
        if ($slot->status === 'maintenance') {
            return response()->json([
                'status' => false,
                'message' => 'This slot is under maintenance and cannot be booked.',
            ], 409);
        }

        //  إذا محجوز بنفس التاريخ
        $exists = StadiumSlotBooking::where('stadium_slot_id', $slot->id)
            ->where('date', $request->date)
            ->where('status', 'booked')
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'This slot is already booked for this date.',
            ], 409);
        }

        //  إنشاء حجز
        $booking = StadiumSlotBooking::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Slot booked successfully.',
            'data' => $booking,
        ], 201);
    }


    public function cancel($id)
    {

        $book = StadiumSlotBooking::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Book not found to cancel it.'
            ], 404);
        }

        $user = auth()->user();

        $isBookingOwner = $user->id === (int) $book->user_id;
        $isStadiumOwner = $book->stadium && $user->id === (int) $book->stadium->user_id;
        $isAdmin = $user->hasRole('admin');

        if (!($isBookingOwner || $isStadiumOwner || $isAdmin)) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Unauthorized to cancel this booking'
            ], 403);
        }
        // $now=Carbon::now();
        // $difference=$book->date-$now;



        $book->update(['status'=>'cancelled']);
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Book cancelled successfully',
            // 'difference'=>$now
        ], 200);
    }



    public function view($id){
        $book = StadiumSlotBooking::with(['stadium', 'slot', 'user'])->find($id);
        if (!$book) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Book not found to show it.'
            ]);
        }

        $user = auth()->user();
        $isBookingOwner = $user->id === (int) $book->user_id;
        $isStadiumOwner = $book->stadium && $user->id === (int) $book->stadium->user_id;
        $isAdmin = $user->hasRole('admin');

        if (!($isBookingOwner || $isStadiumOwner || $isAdmin)) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Unauthorized to show this booking'
            ], 403);
        }
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Booking retrieved successfully',
            'data' => [
                'Stadium' => $book
            ]
        ], 200);
    }



    public function viewAll($id){          //عرض كل حجوزات الملعب, بمرققق الملعب
        $stadium=Stadium::find($id);
        if (!$stadium) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Stadium not found to show booking in this stadium .'
            ]);
        }

        $user = auth()->user();
        if ($user->id !== $stadium->user_id ) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Unauthorized to view bookings of this stadium.'
            ]);
        }
        $bookings = StadiumSlotBooking::where('stadium_id', $id)->get();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Bookings retrieved successfully.',
            'data' => $bookings
        ]);

    }

}
