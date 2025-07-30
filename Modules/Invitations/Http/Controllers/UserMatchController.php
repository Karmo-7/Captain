<?php

namespace Modules\Invitations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Invitations\Entities\UserMatch;

class UserMatchController extends Controller
{
    public function index()
    {
        return UserMatch::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
             'match_invitation_id' => 'required|exists:invitation_matches,id',
        ]);

        return UserMatch::create($data);
    }

    public function show(UserMatch $userMatch)
    {
        return $userMatch;
    }

    public function update(Request $request, UserMatch $userMatch)
    {
        $data = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
          'match_invitation_id' => 'required|exists:invitation_matches,id',
        ]);

        $userMatch->update($data);
        return $userMatch;
    }

    public function destroy(UserMatch $userMatch)
    {
        $userMatch->delete();
        return response()->noContent();
    }
}
