<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Applicant;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;

class HomeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $meetings = Meeting::all()->count();
        $meetingAgendas = MeetingAgenda::all()->count();
        $applicants = Applicant::all()->count();
        return view('home', compact('applicants', 'meetings', 'meetingAgendas'));
    }
}
