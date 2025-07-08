<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestType;
use App\Models\User;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    //

    /**
     * Display Timesheet List Page.
     */
    public function index(Request $request)
    {
        // Check authorization
        // if (!auth()->user()->can('leave-requests')) {
        //     return abort('403');
        // }

        $current_user = auth()->user()->id;

        // Check if Manager Role => Get all Requests
        // WIP

        // If not => Get own Requests
        $requests = LeaveRequest::where('user_id', $current_user)->get();

        // Other data
        $request_types = LeaveRequestType::pluck('name', 'id');

        $data = [
            'requests' => $requests,
            'request_types' => $request_types,
        ];

        return view('leave_requests.index', $data);
    }

    public function add(Request $request)
    {
        // Get current users
        $current_user = auth()->user()->id;
        $users = [];

        // Check if Manager Role => Get all Users
        // WIP

        // If not => Can only create own Requests        
        array_push($users, $current_user);

        $data = [
            'users' => $users,
        ];

        return view('leave_requests.edit', $data);
    }

    public function edit(Request $request)
    {
        // Get current request
        $input = $request->all();
        $id = $input['id'] ?? '';

        if (!$id) {
            // Show add new form
            $curr_request = new LeaveRequest;

            // Check authority
            // If Manager => show all

            // If User => show current user
            $user_select = User::where('id', auth()->user()->id)->get();
            $readonly = false;
        } else {
            // Show edit form
            $curr_request = LeaveRequest::find($id);
            $curr_request->date_start = Carbon::createFromFormat('Y-m-d H:i:s', $curr_request->time_start)->format('d/m/Y');
            $curr_request->time_start = Carbon::createFromFormat('Y-m-d H:i:s', $curr_request->time_start)->format('H:i');
            $curr_request->date_end = Carbon::createFromFormat('Y-m-d H:i:s', $curr_request->time_end)->format('d/m/Y');
            $curr_request->time_end = Carbon::createFromFormat('Y-m-d H:i:s', $curr_request->time_end)->format('H:i');
            $user_select = User::where('id', $curr_request->user_id)->get();
            $readonly = true;
        }

        $user_select_html = '';
        foreach ($user_select as $user) {
            $user_select_html .= "<option value='" . $user->id . "'>" . $user->name . "</option>";
        }

        $data = [
            'curr_request' => $curr_request,
            'user_select' => $user_select,
            'user_select_html' => $user_select_html,
            'readonly' => $readonly
        ];

        return $data;
    }

    public function update(Request $request)
    {
        // Get current request
        $input = $request->all();
        $data = $input['input'] ?? '';

        $data = array_column($data, 'value', 'name');
        $data['date_start'] .= ' ' . $data['time_start'];
        $data['date_end'] .= ' ' . $data['time_end'];
        $data['time_start'] = Carbon::createFromFormat('d/m/Y H:i', $data['date_start'])->format('Y-m-d H:i:s');
        $data['time_end'] = Carbon::createFromFormat('d/m/Y H:i', $data['date_end'])->format('Y-m-d H:i:s');
        unset($data['date_start']);
        unset($data['date_end']);

        if($data['id']) {
            $curr_request = LeaveRequest::find($data['id']);
            $curr_request->status = $data['status'];
        } else {
            $curr_request = new LeaveRequest;
            $curr_request->status = 2;
            $curr_request->created_by = auth()->user()->id;
        }
        
        foreach ($data as $key => $value) {
            $curr_request->$key = $value;
        }

        $curr_request->save();

        return 1;
    }

    public function list(Request $request)
    {
        // Check authorization
        // if (!auth()->user()->can('timesheet')) {
        //     return abort('403');
        // }

        LeaveRequest::list($request);
    }
}
