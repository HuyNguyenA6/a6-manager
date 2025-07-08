<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\TimesheetReportItem;
use App\Constant\TimesheetConstant;
use Carbon\Carbon;

class TimesheetReport extends Model
{
    //
    protected $table = "timesheet";

    protected $fillable = [
        'start_date',
        'status',
        'submitted_at',
        'work_hours',
        'comments',
        'reject_reason'        
    ];

    public static function list(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'week_start',
            2 => 'user',
            3 => 'profile',
            4 => 'submitted_at',
            5 => 'work_hours',
            6 => 'approver',
            7 => 'status',
            8 => 'comments',
            9 => 'action',
        );
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $date_input = $request->columns[1]['search']['value'] ?? 'null';
        $user_id    = $request->columns[2]['search']['value'] ?? 'null';
        $timesheet_profile_id = $request->columns[3]['search']['value'] ?? 'null';

        if ($user_id == 'null') $user_id = '';
        if ($date_input == 'null') $date_input = '';
        if ($timesheet_profile_id == 'null') $timesheet_profile_id = '';

        $timesheet_query = TimesheetReport::_getTimesheets($user_id, $date_input, null, $timesheet_profile_id);

        $totalData = $timesheet_query->count();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');

            $timesheet_query = $timesheet_query
                ->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%")
                        ->orWhere('timesheet_profiles.name', 'like', "%$search%")
                        ->orWhere('approver', 'like', "%$search%")
                        ->orWhere('timesheet.comment', 'like', "%$search%")
                        ->orWhere('timesheet.status', 'like', "%$search%")
                        ->orWhere('timesheet.week_start', 'like', "%$search%");
                });
        }

        $totalFiltered = $timesheet_query->count();

        $timesheets = $timesheet_query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = array();

        $request_type = LeaveRequestType::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        $current_user = auth()->user()->id;

        foreach ($timesheets as $timesheet) {
            $viewLink = route('timesheets.view', ['id' => $timesheet->id], false);
            $nestedData['id']                   = $timesheet->id;
            $nestedData['week_start']           = "<a href='" . $viewLink . "'>" . $timesheet->start_date . "</a>";
            $nestedData['user']                 = isset($users[$timesheet->user_id]) ? $users[$timesheet->user_id] : $timesheet->user_id;
            $nestedData['approver']             = isset($users[$timesheet->approver_id]) ? $users[$timesheet->approver_id] : $timesheet->approver_id;
            $nestedData['profile']              = $timesheet->profile;
            $nestedData['work_hours']           = $timesheet->work_hours;

            $comment = $timesheet->comments ? '<span data-toggle="tooltip" title="' . $timesheet->comments . '"><i class="fa fa-comments" aria-hidden="true"></i></span>' : '';
            // $comment = $timesheet->comment;
            $nestedData['comments']              = $comment;

            $nestedData['hour']                = number_format($timesheet->hour, 2);

            switch ($timesheet->status) {
                case TimesheetConstant::REPORT_STATUS_DRAFT:
                    $status_color               = "secondary";
                    break;
                case TimesheetConstant::REPORT_STATUS_SUBMITTED:
                    $status_color               = "info";
                    break;
                case TimesheetConstant::REPORT_STATUS_APPROVED:
                    $status_color               = "success";
                    break;
                case TimesheetConstant::REPORT_STATUS_REJECTED:
                    $status_color               = "danger";
                    break;
                default:
                    $status_color               = "";
                    break;
            }
            $nestedData['status']               = '<span class="badge badge-' . $status_color . '">' . config('util.leave_status')[$timesheet->status] . '</span>';

            $editButton = '';
            $deleteButton = '';
            $approveButton = '';

            if ($timesheet->status == TimesheetConstant::REPORT_STATUS_DRAFT || $timesheet->status == TimesheetConstant::REPORT_STATUS_REJECTED) {
                if ($timesheet->user_id == $current_user) {
                    $editLink = route('timesheets.edit', ['id' => $timesheet->id], false);
                    $editButton = "<a href='" . $editLink . "' class='btnEditTimesheet d-sm-inline-block btn btn-sm btn-primary shadow-sm' data-id='" . $timesheet->id . "' data-toggle='tooltip' data-placement='top' title='Edit'><i class='fas fa-pen'></i></a>";
                    $deleteButton = "<button class='btnDelete d-sm-inline-block btn btn-sm btn-danger shadow-sm' data-id=" . $timesheet->id . " data-toggle='tooltip' data-placement='top' title='Delete'><i class='fa fa-trash'></i></button>";    
                }
            }

            // $allow_approve = auth()->user()->hasRole('System Admin') || $timesheet->user_id != auth()->user()->id ? 1 : 0;
            // if (auth()->user()->can('timesheet.request.approve') && $allow_approve) {
            // if (auth()->user()->can('timesheet.request.approve')) {
            if ($timesheet->status == TimesheetConstant::REPORT_STATUS_SUBMITTED && $timesheet->user_id != $current_user) {
                $approveButton = "<button class='d-sm-inline-block btn btn-sm btn-success shadow-sm btnApprove' data-id=" . $timesheet->id . " data-toggle='tooltip' data-placement='top' title='Approve Action'><i class='fa fa-check'></i></button>";
            }
            // }

            $nestedData['action'] = $editButton . ' ' . $deleteButton . ' ' . $approveButton;
            $nestedData['submitted_at'] = '';
            if($timesheet->submitted_at) {
                $nestedData['submitted_at']           = Carbon::createFromFormat('Y-m-d H:i:s', $timesheet->submitted_at)->format('d/m/Y H:i');;
            }            
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
     * Internal: Get Timesheet Reports
     */
    public static function _getTimesheets($user_id = null, $date_input = null, $status = null, $timesheet_profile_id = null)
    {
        if ($date_input == '1') $date_input = '';

        $request_query = $date_query = TimesheetReport::leftJoin('users', 'users.id', '=', 'timesheet.user_id')
            ->leftJoin('users as approvers', 'approvers.id', '=', 'timesheet.approver_id')
            ->leftJoin('timesheet_profile_approvers as profile_approvers', 'profile_approvers.timesheet_profile_id', 'users.timesheet_profile_id')
            ->leftJoin('timesheet_profiles as profiles', 'profiles.id', '=', 'users.timesheet_profile_id')
            ->select(
                'timesheet.id',
                'timesheet.start_date',
                'timesheet.approver_id',
                'timesheet.user_id',
                'timesheet.comments',
                'timesheet.work_hours',
                'timesheet.status',
                'timesheet.submitted_at',
                'timesheet.reject_reason',
                'users.name as user',
                'approvers.name as approver',
                'profiles.name as profile'
            );


        $totalData  = $request_query->count();

        if ($user_id) {
            $request_query->where('user_id', '=', $user_id);
        }

        if ($status) {
            $request_query->where('status', '=', $status);
        }

        if ($timesheet_profile_id) {
            $request_query->where('users.timesheet_profile_id', '=', $timesheet_profile_id);
        }

        $request_query = $request_query
            ->where(function ($query) {
                $query->where('timesheet.user_id', '=', auth()->user()->id)
                    ->orWhere(function ($subquery) {
                        $subquery->where('timesheet.approver_id', '=', auth()->user()->id)
                            ->where('timesheet.status', '!=', TimesheetConstant::REPORT_STATUS_DRAFT);
                    })
                    ->orWhere(function ($subquery) {
                        $subquery->where('profile_approvers.user_id', '=', auth()->user()->id)
                            ->where('timesheet.status', '!=', TimesheetConstant::REPORT_STATUS_DRAFT);
                    });
                // $query->where('timesheet.user_id', '=', auth()->user()->id)
                //     ->orWhere('timesheet.approver_id', '=', auth()->user()->id)
                //     ->orWhere('profile_approvers.user_id', auth()->user()->id);
            });

        // if (auth()->user()->hasAnyRole('System Admin', 'Project Manager', 'Project Coordinator', 'Subcontractor Admin')) {
        // if (auth()->user()->hasAnyRole('System Admin', 'Project Manager')) {
            // Show all
        // } else if (auth()->user()->hasAnyRole('Subcontractor Manager')) {
            // $request_query->where('users.timesheet_profile_id', '=', auth()->user()->timesheet_profile_id);
        // } else {
            // $request_query->where('user_id', '=', auth()->user()->id);
        // }

        if ($date_input) {
            $dates = explode(' ~ ', $date_input);
            switch (count($dates)) {
                case 2:
                    $request_query->where('start_date', '>=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'))
                        ->where('start_date', '<=', date_create_from_format('d/m/Y', $dates[1])->format('Y-m-d'));
                    break;
                case 1:
                    if ($dates[0])
                        $request_query->where('start_date', '=', date_create_from_format('d/m/Y', $dates[0])->format('Y-m-d'));
                    break;
                default:
                    break;
            }
        }

        return $request_query;
    }
}
