<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetReportItem extends Model
{
    //
    protected $table = "timesheet_items";

    protected $fillable = [
        'report_id',
        'activity_type',
        'project_code',
        'comment',
        'hour_1',
        'day_1',
        'hour_2',
        'day_2',
        'hour_3',
        'day_3',
        'hour_4',
        'day_4',
        'hour_5',
        'day_5',
        'hour_6',
        'day_6',
        'hour_7',
        'day_7'
    ];
}
