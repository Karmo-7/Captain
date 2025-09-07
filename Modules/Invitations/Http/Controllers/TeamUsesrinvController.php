<?php

namespace Modules\Invitations\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Invitations\Entities\Team_Usesrinv;
use Modules\Invitations\Events\InvitationSent;
use Modules\Invitations\Events\InvitationAccepted;

class TeamUsesrinvController extends Controller
{
    // ✅ دوال الرد الموحد
    protected function successResponse($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Something went wrong', $code = 400, $data = null)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    // جلب كل الدعوات
    public function index()
    {
        $data = Team_Usesrinv::all()->map(fn($inv) => $this->formatInvitation($inv));
        return $this->successResponse($data, 'All invitations retrieved successfully');
    }

    // إنشاء دعوة
   public function store(Request $request)
{
    try {
        $request->validate([
            'status' => 'nullable|in:pending,accepted,declined',
            'sent_at' => 'nullable|string|max:45',
            'team_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'is_team' => 'required|boolean',
        ]);

        // تحقق من وجود receiver حسب نوع الدعوة
        if ($request->is_team) {
            $request->validate(['receiver_id' => 'exists:users,id']);
        } else {
            $request->validate(['receiver_id' => 'exists:teams,id']);
        }

        // ✅ تحقق إذا الدعوة موجودة مسبقًا
        $existingInvitation = Team_Usesrinv::where('team_id', $request->team_id)
            ->where('receiver_id', $request->receiver_id)
            ->where('is_team', $request->is_team)
            ->first();

        if ($existingInvitation) {
            return $this->errorResponse('Invitation already exists', 400);
        }

        // إنشاء الدعوة
        $invitation = Team_Usesrinv::create($request->all());

        // تحميل العلاقات قبل إطلاق الحدث
        $invitation->load(['sender', 'receiver']);

        // إطلاق الحدث
        event(new InvitationSent($invitation));

        return $this->successResponse($this->formatInvitation($invitation), 'Invitation created successfully', 201);

    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage(), 400);
    }
}

    // جلب دعوة واحدة
    public function show($id)
    {
        $inv = Team_Usesrinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Invitation not found', 404);
        }
        return $this->successResponse($this->formatInvitation($inv), 'Invitation retrieved successfully');
    }

    // تحديث دعوة
    public function update(Request $request, $id)
    {
        $inv = Team_Usesrinv::find($id);
        if (!$inv) {
            return $this->errorResponse('Invitation not found', 404);
        }

        $request->validate([
            'status' => 'nullable|in:pending,accepted,declined',
            'sent_at' => 'nullable|string|max:45',
            'team_id' => 'sometimes|integer|exists:teams,id',
            'receiver_id' => 'sometimes|integer',
            'is_team' => 'sometimes|boolean',
        ]);

        $inv->update($request->all());
        $inv->load(['sender', 'receiver']);

        return $this->successResponse($this->formatInvitation($inv), 'Invitation updated successfully');
    }

    // حذف دعوة
    public function destroy($id)
    {
        $deleted = Team_Usesrinv::destroy($id);
        if (!$deleted) {
            return $this->errorResponse('Invitation not found or not deleted', 404);
        }
        return $this->successResponse(null, 'Invitation deleted successfully');
    }

    // الدعوات حسب النوع
    public function invitationsSentByUser($userId)
    {
        $data = Team_Usesrinv::sentByUser($userId)->get()->map(fn($inv) => $this->formatInvitation($inv));
        return $this->successResponse($data, 'Invitations sent by user retrieved successfully');
    }

    public function invitationsReceivedByUser($userId)
    {
        $data = Team_Usesrinv::receivedByUser($userId)->get()->map(fn($inv) => $this->formatInvitation($inv));
        return $this->successResponse($data, 'Invitations received by user retrieved successfully');
    }

    public function invitationsSentByTeam($teamId)
    {
        $data = Team_Usesrinv::sentByTeam($teamId)->get()->map(fn($inv) => $this->formatInvitation($inv));
        return $this->successResponse($data, 'Invitations sent by team retrieved successfully');
    }

    public function invitationsReceivedByTeam($teamId)
    {
        $data = Team_Usesrinv::receivedByTeam($teamId)->get()->map(fn($inv) => $this->formatInvitation($inv));
        return $this->successResponse($data, 'Invitations received by team retrieved successfully');
    }

    // الموافقة على الدعوة
    public function approveInvitation(Request $request, $id)
    {
        $invitation = Team_Usesrinv::find($id);
        if (!$invitation) {
            return $this->errorResponse('Invitation not found', 404);
        }

        if ($invitation->status === 'accepted') {
            return $this->errorResponse('Invitation already accepted', 400);
        }


$invitation->update(['status' => 'accepted']);

        event(new InvitationAccepted($invitation));

        // تحديث team_id في Profile إذا موجود
        if ($invitation->receiver_id && $invitation->is_team) {
            $profile = Profile::where('user_id', $invitation->receiver_id)->first();
            if ($profile) {
                $profile->update(['team_id' => $invitation->team_id]);
            }
        }

        return $this->successResponse($this->formatInvitation($invitation), 'Invitation approved and user linked to team successfully');
    }

    // رفض الدعوة
    public function rejectInvitation($id)
    {
        $invitation = Team_Usesrinv::find($id);
        if (!$invitation) {
            return $this->errorResponse('Invitation not found', 404);
        }

        if ($invitation->status === 'declined') {
            return $this->errorResponse('Invitation already declined', 400);
        }

        $invitation->update(['status' => 'declined']);
        $invitation->load(['sender', 'receiver']);
event(new \Modules\Invitations\Events\InvitationDeclined($invitation));
        return $this->successResponse($this->formatInvitation($invitation), 'Invitation rejected successfully');
    }

    // دالة مساعدة لتنسيق الدعوة
private function formatInvitation($inv)
{

    // Safely get sender and receiver names


    return [
        'id' => $inv->id,
        'status' => $inv->status,
        'sent_at' => $inv->sent_at,
        'is_team' => $inv->is_team,
        'team_id' => $inv->team_id,
        'receiver_id' => $inv->receiver_id,
        'created_at' => $inv->created_at,
        'updated_at' => $inv->updated_at,
    ];
}
}
