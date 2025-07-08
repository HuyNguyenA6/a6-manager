@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Policy</h1>
@stop

@section('js') 
    @vite(['resources/js/app.js'])
@stop

@section('css')
    @vite(['resources/css/global.css'])
@stop

@section('content')
    <div id="container-user">
        <ul>
            <li>Each employee can have 1 day(s) of WFH per week</li>
            <li>OT time are counted as followed:</li>
            <ul>
                <li>x1.5 for weekdays</li>
                <li>x2.0 for weekends</li>
                <li>x3.0 for holidays</li>
            </ul>
        </ul>
    </div>
    @include('timesheets._modals.delete_modal')
@stop