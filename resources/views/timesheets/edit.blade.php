@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Timesheets</h1>
@stop

@section('js') 
    @vite(['resources/js/app.js'])
    @vite(['resources/js/timesheets/edit.js'])
@stop

@section('css')
    @vite(['resources/css/global.css'])
    @vite(['resources/css/util/dataTables.dataTables.min.css'])
    @vite(['resources/css/timesheets/edit.css'])
@stop

@section('content')    
    <div id="container-user">
        @if(isset($timesheet->id))
        <h1 class="h3 mb-0 pb-1 text-gray-800 border-bottom-title">
            Edit Timesheet Report
        </h1>
            @include('timesheets._modals.reason_reject', ['action' => route('timesheets.reject', ['id' => $timesheet->id])])
        <form id="frmReport" method="POST" action="{{ route('timesheets.update', ['id' => $timesheet->id], false) }}" >            
            @method('PUT')
        @else
        <h1 class="h3 mb-0 pb-1 text-gray-800 border-bottom-title">
            Add Timesheet Report
        </h1>
        <form id="frmReport" method="POST" action="{{ route('timesheets.store', false) }}" >
        @endif
            @csrf
            <input type="hidden" id="add_work_hour_link" class="for-desktop" value="{{ route('timesheets.add_work_hour',[],false) }}">
            <input type="hidden" id="add_work_hour_link" class="for-mobile" value="{{ route('timesheets.add_work_hour_on_mobile',[],false) }}">
            <div class="row mt-12 pt-4">
                <div class="col-xl-2 col-4 font-13-for-mobile">
                    <div class="form-group">
                        <label for="user" >User</label>
                    </div>
                </div>
                <div class="col-xl-4 col-8">
                    <div class="form-group">
                        <input type="text" class="form-control" id="user_name" value="{{ $timesheet->user_name ?? auth()->user()->name }}" readonly>
                        <input type="hidden" class="form-control" id="user_id" value="{{ $timesheet->user_id ?? auth()->user()->id }}" readonly>
                    </div>
                </div>
                <div class="col-xl-6 col-12"></div>

                <div class="col-xl-2 col-4 font-13-for-mobile">
                    <div class="form-group">
                        <label for="start_date" class="required" >Week Start</label>
                    </div>
                </div>
                <div class="col-xl-4 col-8">
                    <div class="form-group">
                        <div class="input-group">
                            @if(!isset($timesheet->id) || $timesheet->status == 0 || $timesheet->status == 3)
                                <input type="hidden" id="original_date" value="">
                                <input type="text" class="date-picker form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $timesheet->start_date ?? '') }}" data-language="en" data-date-format="dd/mm/yyyy" required readonly style="background-color: white">
                                <div class="input-group-append show-datepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            @else
                                <input type="text" id="start_date" name="start_date" class="form-control" value="{{ $timesheet->start_date }}" readonly>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-12"></div>

                <div class="col-xl-2 col-4 font-13-for-mobile">
                    <div class="form-group">
                        <label for="comment">Hours Worked</label>
                    </div>
                </div>
                <div class="col-xl-4 col-8">
                    <div class="form-group">
                        <input type="text" class="form-control" id="hours_worked" name="work_hours" value="" readonly>
                    </div>
                </div>
                <div class="col-xl-6 col-12"></div>

                <div class="col-xl-2 col-4 font-13-for-mobile">
                    <div class="form-group">
                        <label for="approver_id" class="required">Approver</label>
                    </div>
                </div>
                <div class="col-xl-4 col-8">
                    <div class="form-group">
                        <input type="hidden" name="approver_id" value="{{ $approver->id ?? '' }}">
                        <input type="text" class="form-control" value="{{ $approver->name ?? '' }}" readonly>

                        <!-- Ajax error -->
                        <span class="invalid-feedback" role="alert" id="start_date_error">
                            <strong id="start_date_error_msg"></strong>
                        </span>
                    </div>
                </div>
                <div class="col-xl-6 col-12"></div>

                <div class="col-xl-2 col-4 font-13-for-mobile">
                    <div class="form-group">
                        <label for="approver_id" class="required">Status</label>
                    </div>
                </div>
                <div class="col-xl-4 col-8">
                    <div class="form-group">
                        <input type="text" class="form-control" id="status" name="status" value="{{ config('util.timehsheet_status')[$timesheet->status] ?? 'New' }}" readonly>
                    </div>
                </div>
                <div class="col-xl-6 col-12"></div>
            </div>
            <div class="row mt-12 pt-4">
                <div class="table-responsive for-desktop" style="overflow-x: hidden;">
                    <table class="table table-bordered compact hover nowrap timesheet-table data_reportDate" style="white-space: nowrap; width: 100%;" id="dataTable_reportDate" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="150" class="align-top">Type</th>
                                <th width="200" class="align-top">Project</th>
                                <th width="200" class="align-top">Activity/Comment</th>
                                <th width="100" class="text-center">Mon<br>
                                    <span id="monday_field"></span></th>
                                <th width="100" class="text-center">Tue<br>
                                    <span id="tuesday_field"></span></th>
                                <th width="100" class="text-center">Wed<br>
                                    <span id="wednesday_field"></span></th>
                                <th width="100" class="text-center">Thu<br>
                                    <span id="thursday_field"></span></th>
                                <th width="100" class="text-center">Fri<br>
                                    <span id="friday_field"></span></th>
                                <th width="100" class="text-center">Sat<br>
                                    <span id="saturday_field"></span></th>
                                <th width="100" class="text-center">Sun<br>
                                    <span id="sunday_field"></span></th>
                                <th width="100" class="align-top">Action</th>
                            </tr>
                        </thead>
                        <tbody id="table_reportDate">
                            <!-- CURRENT TIMESHEET ITEMS -->
                            @foreach ($timesheet_items as $key => $item)
                            <tr class="time_row">
                                <td>
                                    <select name="timesheet_current[{{$item->id}}][activity_type]" class="form-control project_number project_number_field" {{$readonly}}>
                                        <option></option>
                                        @foreach ($timesheet_types as $type_id => $type)
                                            <option value="{{$type_id}}" @if($item->activity_type == $type_id) selected @endif >{{$type}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" class="form-control job_type" name="timesheet_current[{{$item->id}}][project_code]" value="{{$item->project_code}}" {{$readonly}}></td>
                                <td><input type="text" class="form-control" name="timesheet_current[{{$item->id}}][comment]" value="{{$item->comment}}" {{$readonly}}></td>

                                @for($i = 1 ; $i < 8 ; $i++)
                                    <td>
                                        <input
                                            type="text" class="form-control work_hours item-nt p-2"
                                            name='timesheet_current[{{$item->id}}][{{ "hour_$i" }}]'
                                           
                                            {{$readonly}}
                                            value="{{$item->{"hour_$i"} ?? ''}}"
                                            data-index="{{$i}}"
                                            data-id="{{$item->id}}" min="0"
                                        >
                                    </td>
                                @endfor

                                <td>
                                    @if(!$readonly)
                                        <a href="#" class='d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm btnDelete' data-id="delete_{{ $item->id }}" ><i class='fa fa-trash'></i></a><input type="hidden" name="timesheet_current[{{$item->id}}][delete]" id="delete_{{ $item->id }}" value="">
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            <!-- NEW TIMESHEET ITEMS -->
                            @if($readonly == '')
                                @for($i = 0; $i < 10; $i++)
                                    @include('timesheets._partials.report_newline', ['i' => $i])
                                @endfor
                            @endif
                        </tbody>
                        <tfoot class="table table-border-transparent table-footer">
                            @if($readonly == '')
                                <tr id="add_row">
                                    <td><a href="#" class='d-sm-inline-block btn btn-sm btn-primary shadow-sm btnAdd' data-id="" ><i class='fa fa-plus'></i> Add Row</a></td>
                                    <td colspan="10"><input type="hidden" id="index" value="10"></td>
                                </tr>
                            @endif

                            <tr>
                                <td></td>
                                <td colspan="2" class="align-bottom"><b>Subtotals</b></td>
                                @for($i = 1 ; $i < 8 ; $i++)
                                    <td>
                                        <input type="text" min="0" name='{{ "subtotal[{$i}]" }}' id='{{ "subtotal_nt_{$i}" }}' class='form-control {{ "subtotal_nt_{$i}" }}' readonly>
                                    </td>
                                @endfor
                                <td></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td><b>Total</b></td>
                                <td colspan="9">
                                    <input type="text" min="0" name='{{ "total" }}' id='{{ "total" }}' class='form-control total_count {{ "total" }}' readonly>
                                </td>
                            </tr>

                            @if($show_reject_reason)
                                <tr>
                                    <td><b>Reason Reject:</b></td>
                                    <td colspan="10">{!! nl2br($timesheet->reject_reason) !!}</td>
                                </tr>
                            @endif

                            <tr>
                                <td><b>Comment</b></td>
                                <td colspan="10"><textarea class="form-control" rows="5" name="comments">{{ $timesheet->comments ?? '' }}</textarea></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <input type="hidden" id="monday_input" name="dates[1]">
                <input type="hidden" id="tuesday_input" name="dates[2]">
                <input type="hidden" id="wednesday_input" name="dates[3]">
                <input type="hidden" id="thursday_input" name="dates[4]">
                <input type="hidden" id="friday_input" name="dates[5]">
                <input type="hidden" id="saturday_input" name="dates[6]">
                <input type="hidden" id="sunday_input" name="dates[7]">
                
                <div class="float-right">
                    @if($show_submit)
                        <button id="btnSave" type="submit" name="action" value="1" class="btn btn-success">Save</button>
                        <button id="btnSubmit" type="submit" name="action" value="2" class="btn btn-primary btn-submit-form">Submit</button>
                    @else
                        @can('timesheet.export')
                            <button id="btnSave" type="submit" name="action" value="1" class="btn btn-success">Save</button>
                        @endcan
                    @endif
                    @if($timesheet->user_id != auth()->user()->id)
                        @if($show_approve)
                            @if(!in_array($timesheet->status, [App\Constant\TimesheetConstant::REPORT_STATUS_APPROVED]))
                                <button id="btnApprove" type="submit" name="action" value="{{ App\Constant\TimesheetConstant::REPORT_ACTION_APPROVE }}" class="btn btn-success btn-submit-form">Approve</button>
                            @endif
                            @if(!in_array($timesheet->status, [App\Constant\TimesheetConstant::REPORT_STATUS_REJECTED]))
                                <button id="btnReject" type="submit" name="action" value="{{ App\Constant\TimesheetConstant::REPORT_ACTION_REJECT }}" class="btn btn-danger">Reject</button>
                            @endif
                        @endif
                    @endif
                    <a href="{{ route('timesheets.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
            <br class="clearfix">
        </form>
    </div>
@stop