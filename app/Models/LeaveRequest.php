<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\LeaveRequestType;
use Carbon\Carbon;
use App\Constant\TimesheetConstant;
// use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    //
    use SoftDeletes;
    // use LogsActivity;
    protected $table = "leave_requests";

    public static function list(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'time_start',
            2 => 'time_end',
            3 => 'hour',
            4 => 'user',
            5 => 'coment',
            6 => 'comment',
            7 => 'request_type',
            8 => 'created_at',
            9 => 'status',
            10 => 'action',
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $date_input = $request->columns[1]['search']['value'] ?? 'null';
        $user_id    = $request->columns[2]['search']['value'] ?? 'null';
        $company_id = $request->columns[3]['search']['value'] ?? 'null';

        if ($user_id == 'null') $user_id = '';
        if ($date_input == 'null') $date_input = '';
        if ($company_id == 'null') $company_id = '';

        $request_query = LeaveRequest::_getLeaveRequests($user_id, $date_input, null, $company_id);

        $totalData = $request_query->count();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');

            $request_query = $request_query
                ->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%")
                        ->orWhere('leave_request_types.name', 'like', "%$search%")
                        ->orWhere('leave_requests.comment', 'like', "%$search%")
                        ->orWhere('leave_requests.status', 'like', "%$search%")
                        ->orWhere('leave_requests.time_start', 'like', "%$search%")
                        ->orWhere('leave_requests.time_end', 'like', "%$search%");
                });
        }

        $totalFiltered = $request_query->count();

        $requests = $request_query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = array();

        $request_type = LeaveRequestType::pluck('name', 'id');
        $users = User::pluck('name', 'id');

        foreach ($requests as $leave_request) {
            $nestedData['id']                   = $leave_request->id;
            $nestedData['time_start']           = Carbon::createFromFormat('Y-m-d H:i:s', $leave_request->time_start)->format('d/m/Y H:i');
            $nestedData['time_end']             = Carbon::createFromFormat('Y-m-d H:i:s', $leave_request->time_end)->format('d/m/Y H:i');
            $nestedData['user']                 = isset($users[$leave_request->user_id]) ? $users[$leave_request->user_id] : $leave_request->user_id;
            $nestedData['request_type']         = '<span class="badge" style="color: ' . $leave_request->text_color . '; background-color: ' . $leave_request->bg_color . '">' . $leave_request->request_type  . '</span>';

            // $comment = $leave_request->comment ? '<span data-toggle="tooltip" title="' . $leave_request->comment . '"><i class="fa fa-comments" aria-hidden="true"></i></span>' : '';
            $comment = $leave_request->comment;
            $nestedData['comment']              = $comment;

            $nestedData['hour']                = number_format($leave_request->hour, 2);

            switch ($leave_request->status) {
                case '1':
                    $status_color               = "secondary";
                    break;
                case '2':
                    $status_color               = "info";
                    break;
                case '3':
                    $status_color               = "success";
                    break;
                case '4':
                    $status_color               = "danger";
                    break;
                default:
                    $status_color               = "";
                    break;
            }
            $nestedData['status']               = '<span class="badge badge-' . $status_color . '">' . config('util.leave_status')[$leave_request->status] . '</span>';

            $editButton = '';
            $deleteButton = '';
            $approveButton = '';

            if ($leave_request->status == TimesheetConstant::REPORT_STATUS_REJECTED) {
                $editButton = "<a href='#' class='btnEditRequest d-sm-inline-block btn btn-sm btn-primary shadow-sm' data-id='" . $leave_request->id . "' data-toggle='tooltip' data-placement='top' title='Edit'><i class='fas fa-pen'></i></a>";
            } else {
                $approveButton = "<button class='d-sm-inline-block btn btn-sm btn-success shadow-sm btnApprove' data-id=" . $leave_request->id . " data-toggle='tooltip' data-placement='top' title='Approve Action'><i class='fa fa-check'></i></button>";
            }

            if ($leave_request->status != TimesheetConstant::REPORT_STATUS_APPROVED && $leave_request->status != TimesheetConstant::REPORT_STATUS_SUBMITTED) {                
                $deleteButton = "<button class='btnDelete d-sm-inline-block btn btn-sm btn-danger shadow-sm' data-id=" . $leave_request->id . " data-toggle='tooltip' data-placement='top' title='Delete'><i class='fa fa-trash'></i></button>";
            }

            // $allow_approve = auth()->user()->hasRole('System Admin') || $leave_request->user_id != auth()->user()->id ? 1 : 0;
            // if (auth()->user()->can('timesheet.request.approve') && $allow_approve) {
            // if (auth()->user()->can('timesheet.request.approve')) {
                // $approveButton = "<button class='d-sm-inline-block btn btn-sm btn-success shadow-sm btnApprove' data-id=" . $leave_request->id . " data-toggle='tooltip' data-placement='top' title='Approve Action'><i class='fa fa-check'></i></button>";
            // }

            $nestedData['action']               = $approveButton . ' ' . $editButton . ' ' . $deleteButton;
            $nestedData['created_at']           = Carbon::createFromFormat('Y-m-d H:i:s', $leave_request->created_at)->format('d/m/Y H:i');;
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalFiltered),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        echo json_encode($json_data);
    }

    /**
     * Internal: Get Timesheet Requests
     */
    public static function _getLeaveRequests($user_id = null, $date_input = null, $status = null, $company_id = null)
    {
        if ($date_input == '1') $date_input = '';

        $request_query = $date_query = LeaveRequest::leftJoin('users', 'users.id', '=', 'leave_requests.user_id')
            ->leftJoin('leave_request_types', 'leave_request_types.id', '=', 'leave_requests.request_type')
            ->select(
                'leave_requests.id',
                'leave_requests.time_start',
                'leave_requests.time_end',
                'leave_requests.user_id',
                'leave_requests.comment',
                'leave_requests.hour',
                'leave_requests.status',
                'leave_requests.created_at',
                'users.name as user',
                'leave_request_types.name as request_type',
                'leave_request_types.bg_color',
                'leave_request_types.text_color'
            );


        $totalData  = $request_query->count();

        if ($user_id) {
            $request_query->where('user_id', '=', $user_id);
        }

        if ($status) {
            $request_query->where('status', '=', $status);
        }

        // if (auth()->user()->hasAnyRole('System Admin', 'Project Manager', 'Project Coordinator', 'Subcontractor Admin')) {
        // if (auth()->user()->hasAnyRole('System Admin', 'Project Manager')) {
            // Show all
        // } else if (auth()->user()->hasAnyRole('Subcontractor Manager')) {
        //     $request_query->where('users.company_id', '=', auth()->user()->company_id);
        // } else {
            $request_query->where('user_id', '=', auth()->user()->id);
        // }

        if ($date_input) {
            $dates = explode(' ~ ', $date_input);
            switch (count($dates)) {
                case 2:
                    $request_query->where('time_start', '>=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'))
                        ->where('time_start', '<=', date_create_from_format('d/m/Y', $dates[1])->format('Y-m-d'))
                        ->where('time_end', '>=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'))
                        ->where('time_end', '<=', date_create_from_format('d/m/Y', $dates[1])->format('Y-m-d'));
                    break;
                case 1:
                    if ($dates[0])
                        $request_query->where('time_start', '=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'))
                            ->where('time_end', '=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'));
                    break;
                default:
                    break;
            }
        }

        return $request_query;
    }
}
