<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimesheetProfile;
use App\Models\TimesheetProfileApprover;

class UserController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, $id): View
    {
        $user = User::find($id);
        return view('user.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function profile(Request $request): View
    {
        return view('user.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->_updateUser($request, $id);

        return redirect(route('users.index'));;
    }

    public function _updateUser($request, $id = null)
    {
        $user = User::find($id);
        $input = $request->all();        

        $user->fill($request->except(['_token']));
        $user->save();

        return redirect(route('users.index'));;
    }

    public function test(Request $request)
    {
        $profile = TimesheetProfile::find(1);
        dd($profile->approvers);
        return view('user.edit', [
            'user' => $user,
        ]);
    }
}
