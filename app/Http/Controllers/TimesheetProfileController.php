<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimesheetProfile;
use App\Models\TimesheetProfileApprover;

class TimesheetProfileController extends Controller
{
    /**
     * Display Timesheet List Page.
     */
    public function index(Request $request)
    {        
        $current_user = auth()->user()->id;

        // If not => Get own timesheets
        $profiles = TimesheetProfile::all();
        $users = User::pluck('name', 'id');

        $data = [
            'profiles' => $profiles,
            'users' => $users,
        ];

        return view('profiles.index', $data);
    }

    public function update(Request $request, $id)
    {
        // Get current request
        $input = $request->all();
        $data = $input['input'] ?? '';

        $approvers = $data['approvers'];
        $current_approvers = TimesheetProfileApprover::where('timesheet_profile_id', $id)->get();

        $profile = TimesheetProfile::find($id);
        $profile->fill($request->except(['_token', 'approvers']));
        $profile->approvers()->sync($approvers);
        $profile->save();

        return 1;
    }
}
