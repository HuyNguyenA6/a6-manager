<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WikiController extends Controller
{
    //
    public function policy()
    {
        return view('wiki.time-policy');
    }
}
