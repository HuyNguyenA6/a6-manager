@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Leave/OT Requests</h1>
@stop

@section('js') 
    @vite(['resources/js/app.js'])
    @vite(['resources/js/leave_requests/index.js'])
@stop

@section('css')
    @vite(['resources/css/app.css'])
    @vite(['resources/css/util/dataTables.dataTables.min.css'])
@stop

@section('content')
    <div id="container-user">
        <div class="d-sm-flex mb-4 flex-row-reverse">
            <a class="btnAddRequest d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-3">
                <i class="fas fa-plus fa-s`m text-white-50"></i> Request Leave/OT</a>
        </div>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Requests</h6>
            </div>
            <div class="card-body">              
                <!-- <div class="d-flex mb-2">
                    <div id="selectDatePane" class="input-group mr-2" style="width: 250px">
                        <input id="selectDate" type="text" class="form-control" data-language="en" data-date-format="dd/mm/yyyy" data-range="true" data-multiple-dates-separator=" ~ " readonly style="background-color: white;"/>
                        <div class="input-group-append show-datepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>              
                <hr> -->
                <div class="table-responsive">
                    <table class="table table-bordered display compact table-striped hover" id="dataTable_request" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Time Start</th>
                            <th>Time End</th>
                            <th>Hours</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Comment</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Time Start</th>
                            <th>Time End</th>
                            <th>Hours</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Comment</th>
                            <th>Action</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('leave_requests._modals.edit_request_modal')
@stop