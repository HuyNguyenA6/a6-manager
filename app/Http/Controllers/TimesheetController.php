<?php

namespace App\Http\Controllers;

use App\Constant\TimesheetConstant;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TimesheetReport;
use App\Models\TimesheetReportItem;
use App\Models\TimesheetReportItemType;
use App\Models\TimesheetProfile;
use App\Models\TimesheetProfileApprover;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Redirect;
use Illuminate\Support\Facades\Log;

class TimesheetController extends Controller
{
    /**
     * Display Timesheet List Page.
     */
    public function index(Request $request)
    {
        // Check authorization
        // if (!auth()->user()->can('timesheet')) {
        //     return abort('403');
        // }

        $current_user = auth()->user()->id;

        // Check if Manager Role => Get managing groups
        // $groups = TimesheetProfileApprover::where('approver_id', $current_user)->pluck('timesheet_profile_id');
        // $users = User::where()
        // $timesheets = TimesheetReport::join('users', 'timesheeet.user_id', 'users.id')
        //             ->where('user_id', $current_user)
        //             ->orWhereIn('users.group', $groups)
        //             ->orWhereIn('timesheet.approver_id')->get();

        // If not => Get own timesheets
        $timesheets = TimesheetReport::where('user_id', $current_user)->get();

        $data = [
            'timesheets' => $timesheets,
        ];

        return view('timesheets.index', $data);
    }

    public function list(Request $request)
    {
        // Check authorization
        // if (!auth()->user()->can('timesheet')) {
        //     return abort('403');
        // }

        TimesheetReport::list($request);
    }

    public function add(Request $request)
    {
        // Check authorization
        // if (!auth()->user()->can('timesheet.add')) {
        //     return abort('403');
        // }

        $current_user = auth()->user();
        $timesheet = new TimesheetReport();
        $timesheet->user_id = $current_user->id;
        $timesheet->user_name = $current_user->name;
        $profile = $current_user->timesheet_profile_id;
        if($profile) {
            $approver = TimesheetProfileApprover::where('timesheet_profile_id', $profile)->first();
            $approver = User::find($approver->user_id);
        } else {
            $approver = null;
        }
        $timesheet_types = TimesheetReportItemType::pluck('name', 'id');

        $data = [
            'timesheet_types'       => $timesheet_types,
            'timesheet'             => $timesheet,
            'timesheet_items'          => array(),
            'approver'              => $approver,
            'show_reject_reason'    => 0,
            'readonly'              => '',
            'show_submit'           => 1,
            'show_approve'          => 0,
            'show_approver'         => 1,
        ];

        return view('timesheets.edit', $data);
    }

    public function view(Request $request, $id)
    {
        $timesheet = TimesheetReport::find($id);
        $timesheet_items = TimesheetReportItem::where('report_id', $id)->get();
        $current_user = User::find($timesheet->user_id);
        $timesheet->user_id = $current_user->id;
        $timesheet->user_name = $current_user->name;
        $profile = $current_user->timesheet_profile_id;
        $approver = User::find($timesheet->approver_id);
        $timesheet_types = TimesheetReportItemType::pluck('name', 'id');

        $approvers = TimesheetProfileApprover::where('timesheet_profile_id', $current_user->timesheet_profile_id)->pluck('user_id')->toArray();

        $show_submit = 0;
        $show_approve = 0;

        if (auth()->user()->id != $current_user->id) {
            // Approving other reports
            if (auth()->user()->id == $timesheet->approver_id || in_array(auth()->user()->id, $approvers)) {
                $show_approve = 1;
            } else {
                abort('403');
            }
        }

        $data = [
            'timesheet_types'       => $timesheet_types,
            'timesheet'             => $timesheet,
            'timesheet_items'       => $timesheet_items,
            'approver'              => $approver,
            'show_reject_reason'    => 0,
            'readonly'              => 'readonly',
            'show_submit'           => 0,
            'show_approve'          => $show_approve,
            'show_approver'         => 1,
        ];

        return view('timesheets.edit', $data);
    }

    public function edit(Request $request, $id)
    {
        // Check authorization
        // if (!auth()->user()->can('timesheet.add')) {
        //     return abort('403');
        // }
        
        $timesheet = TimesheetReport::find($id);
        $timesheet_items = TimesheetReportItem::where('report_id', $id)->get();
        $current_user = User::find($timesheet->user_id);
        $timesheet->user_id = $current_user->id;
        $timesheet->user_name = $current_user->name;
        $profile = $current_user->timesheet_profile_id;
        $approver = User::find($timesheet->approver_id);
        $timesheet_types = TimesheetReportItemType::pluck('name', 'id');

        $approvers = TimesheetProfileApprover::where('timesheet_profile_id', $current_user->timesheet_profile_id)->pluck('user_id');

        $show_submit = 0;
        $show_approve = 0;

        if (auth()->user()->id == $current_user->id) {
            // Own timesheet
            if ($timesheet->status == TimesheetConstant::REPORT_STATUS_DRAFT ||
                $timesheet->status == TimesheetConstant::REPORT_STATUS_REJECTED) {
                $show_submit = 1;
            }
        } else {
            return abort('403');
        }

        $data = [
            'timesheet_types'       => $timesheet_types,
            'timesheet'             => $timesheet,
            'timesheet_items'       => $timesheet_items,
            'approver'              => $approver,
            'show_reject_reason'    => 0,
            'readonly'              => '',
            'show_submit'           => $show_submit,
            'show_approve'          => $show_approve,
            'show_approver'         => 1,
        ];

        return view('timesheets.edit', $data);
    }

    public function store(Request $request)
    {
        $this->_updateTimesheet($request);

        return redirect(route('timesheets.index'));
    }

    public function update(Request $request, $id)
    {
        $this->_updateTimesheet($request, $id);

        return redirect(route('timesheets.index'));
    }

    public function delete(Request $request, $id)
    {
        // Check authorization
        $timesheet = TimesheetReport::find($id);

        if ($timesheet != null && $timesheet->user_id == auth()->user()->id) {
            $timesheet->delete();
        }

        return 100;
    }

    public function addWorkHourForReports(Request $request)
    {
        $input = $request->all();
        $index = $input['index'];
        $timesheet_types = TimesheetReportItemType::pluck('name', 'id');

        return view('timesheets._partials.report_newline', array(
            'i' => $index,
            'timesheet_types' => $timesheet_types
        ));
    }

    public function _updateTimesheet($request, $id = null)
    {
        $user = auth()->user();
        $input = $request->all();        

        $action = $input['action'] ?? TimesheetConstant::REPORT_STATUS_DRAFT;

        // dd($action);

        switch ($action) {
            case TimesheetConstant::REPORT_STATUS_APPROVED:
                $this->_approve($request, $id);
                return;
                break;
            case TimesheetConstant::REPORT_STATUS_REJECTED:
                $this->_reject($request, $id); 
                return;
                break;
            default:
                break;
        }

        if(!isset($input['action'])) {
            Log::info('== CREATE TIMESHEET ==');
            Log::info($input);
        }

        if($action == TimesheetConstant::REPORT_STATUS_SUBMITTED) {
            $rules = array(
                'start_date'        => 'required',
                // 'approver_id'       => 'required',
            );
        } else {
            $rules = array();
        }

        if(!empty($rules)) {
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Redirect::back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        if (!$id) {
            $report_check = TimesheetReport::where('user_id', auth()->user()->id)
                        ->where('start_date', $input['start_date'])
                        ->first();
            if($report_check) {
                $message = 'Report for ' . $input['start_date'] . ' already exists.';
                $request->session()->flash('alert-error', $message);
                return Redirect::back()
                    ->withInput();
            }
        }        

        if (!$id) {
            $report = new TimesheetReport();
            $report->user_id = $user->id;
            $report->approver_id = $input['approver_id'] ?? '';
            $report_org_status = TimesheetConstant::REPORT_STATUS_DRAFT;
        } else {
            $report = TimesheetReport::find($id);
            $report_org_status = $report->status;
        }

        $report->fill($request->except(['_token']));
        
        $report->status = TimesheetConstant::REPORT_STATUS_DRAFT;
        if($action == TimesheetConstant::REPORT_ACTION_SUBMIT) {
            $report->submitted_at = Carbon::now();
            $report->status = TimesheetConstant::REPORT_STATUS_SUBMITTED;
        } else if ($action != TimesheetConstant::REPORT_ACTION_SAVE) {
            $report->status = $action;
        }
        
        // $report->start_date = Carbon::createFromFormat('d/m/Y', $input['start_date'])->format('Y-m-d');
        // dd($report);
        $report->save();


        // Existing timesheet items
        $current_input_items = $input['timesheet_current'] ?? array();
        // dd($current_input_items);
        foreach ($current_input_items as $key => $current_input_item) {
            $current_item = TimesheetReportItem::find($key);
            if(isset($current_input_item["delete"]) && $current_input_item["delete"]) {
                $current_item->delete();
            } else {
                $current_item->fill($current_input_item);
                // if($report->status == TimesheetConstant::REPORT_STATUS_DRAFT) {
                //     $current_item->disableLogging();
                // } else {                        
                //     $current_item->enableLogging();
                // }

                $current_item->save();
            }
        }

        // New timesheet Items
        $new_input_items = $input['timesheet_new'] ?? array();
        // dd($new_input_items);
        // dd($current_input_items, $new_input_items);
        $dates = $input['dates'];
        foreach ($new_input_items as $key => $new_input_item) {
            $isArrayNull = true;
            // if ($report_org_status == TimesheetConstant::REPORT_STATUS_DRAFT) {
                $arrayValue = $new_input_item;
                if (count(array_filter($arrayValue)) > 0) {
                    $isArrayNull = false;
                }
            // }
            $new_item = new TimesheetReportItem;
            $new_item->report_id = $report->id;
            $new_item->fill($new_input_item);

            foreach ($dates as $date_key => $date) {
                $day_attr = 'day_' . $date_key;
                $new_item->$day_attr = $date;
            }

            if (!$isArrayNull) {
                // if($report->status == TimesheetConstant::REPORT_STATUS_DRAFT) {
                //     $new_item->disableLogging();
                // } else {                        
                //     $new_item->enableLogging();
                // }
                $new_item->save();
            }
        }

        // Notification
        $approver = User::find($report->approver_id);
        $user_name = User::find(auth()->user()->id)->name;
        switch ($action) {
            case TimesheetConstant::REPORT_STATUS_SUBMITTED:                
                Log::info("[DEBUG][Timesheet] START - Submitting Timesheet for " . $user_name);
                $report->link = route('timesheets.edit', ['id' => $report->id]);
                $report->end_date = Carbon::createFromFormat('d/m/Y', $report->start_date)->addDay(7)->format('d/m/Y');
                if($approver) {
                    $sys_email = env('MAIL_FROM_ADDRESS');
                    $to_emails = [];
                    $to_emails[] = $approver->email;
                    $delegate_approver = $approver->delegate_timesheet_approver ?? '';
                    if($delegate_approver != '') {
                        $delegate_approver = User::find($delegate_approver);
                        $delegate_email = $delegate_approver->email ?? '';
                        if($delegate_email != '') {
                            $to_emails[] = $delegate_email;
                            // $names = [$user_name, $delegate_approver->name];
                            $user_name = $user_name . ', ' . $delegate_approver->name;
                        }
                    }

                    // Mail::to($to_emails)->cc($sys_email)->send(new TimesheetSubmitEmail($approver->name, $user_name, $report));
                    // Notification::sendMessageNotification('New Timesheet Report is waiting for your approval', $report->approver_id, route('timesheets.edit', ['id' => $report->id], false));
                    Log::info('New Timesheet Report #' . $report->id . ' is waiting for approval from ' . $approver->name);
                    Log::info("[DEBUG][Timesheet] END - Submitting Timesheet for " . $user_name);
                }
                break;
            case TimesheetConstant::REPORT_STATUS_APPROVED:
            case TimesheetConstant::REPORT_STATUS_REJECTED:
                if($report->status == TimesheetConstant::REPORT_STATUS_APPROVED) {
                    $action = 'approved';
                } else {
                    $action = 'rejected';
                }
                if($approver) {
                    $sys_email[] = $approver->email;
                }

                #To: User; [Notify On Approval]
                // Mail::to(array_merge([$user->email], ($user->timesheetProfile->notify_approval ?? [])))->cc($sys_email)->send(new TimesheetApproveEmail($user->name, $report));
                // Notification::sendMessageNotification('Your Timesheet Report has been ' . $action, $report->user_id, route('timesheets.report.edit', ['id' => $report->id], false));
                Log::info('Timesheet Report #' . $report->id . ' has been ' . $action . ' by ' . $approver->name);
                Log::info("[DEBUG][Timesheet] END - Updating Timesheet");
                break;

            default:
                // code...
                break;
        }
    }

    public function _approve(Request $request, $id)
    {
        $timesheet = TimesheetReport::find($id);
        $current_user = auth()->user;
        $input = $request->all();

        // Check Authorization
        if (!$this->_checkApprover($id, $current_user->id)) {
            abort('403');
        }

        $timesheet->status = TimesheetConstant::REPORT_STATUS_APPROVED;
        $timesheet->comments = $input['comments'];
        $timesheet->save();
        // return redirect(route('timesheets.index'));
    }

    public function _reject(Request $request, $id)
    {
        $timesheet = TimesheetReport::find($id);
        $current_user = auth()->user();
        $input = $request->all();

        // Check Authorization
        if (!$this->_checkApprover($id, $current_user->id)) {
            abort('403');
        }

        $timesheet->status = TimesheetConstant::REPORT_STATUS_REJECTED;
        $timesheet->comments = $input['comments'];
        $timesheet->save();
        // return redirect(route('timesheets.index'));
    }

    public function _checkApprover($timesheet_id, $user_id)
    {
        $timesheet = TimesheetReport::leftJoin('users', 'users.id', '=', 'timesheet.user_id')
            ->leftJoin('users as approvers', 'approvers.id', '=', 'timesheet.approver_id')
            ->leftJoin('timesheet_profile_approvers as profile_approvers', 'profile_approvers.timesheet_profile_id', 'users.timesheet_profile_id')
            ->leftJoin('timesheet_profiles as profiles', 'profiles.id', '=', 'users.timesheet_profile_id')
            ->where('timesheet.id', $timesheet_id)
            ->where(function ($query) {
                $query->where('timesheet.approver_id', '=', auth()->user()->id)
                    ->orWhere('profile_approvers.user_id', auth()->user()->id);
            })->first();

        return $timesheet;
    }
}
