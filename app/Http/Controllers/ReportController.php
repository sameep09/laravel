<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Country;
use App\Models\Meeting;
use App\Models\Ministry;
use App\Models\Applicant;
use App\Models\StaffPost;
use App\Models\FiscalYear;
use App\Models\StaffGroup;
use App\Models\StaffLevel;
use App\Models\StudyLeave;
use App\Models\ProgramType;
use App\Models\StaffService;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Models\StaffSubGroup;
use App\Models\ReportTemplate;
use App\Models\NominationMeeting;
use App\Http\Requests\ReportRequest;
use App\Models\NominationMeetingAttendee;

class ReportController extends Controller
{

    public function index()
    {
        $title = 'प्रतिवेदनहरू';

        return view('report.index', compact('title'));

        if (request()->session()->has('report_params'))
            request()->session()->forget('report_params');
    }

    public function set_report_param(ReportRequest $request)
    {
        session()->put('report_params', $request->validated());

        return redirect(make_route('report.view-report'));
    }

    public function view_reports()
    {
        $paramData = session('report_params');

        if ($paramData['report_type'] == '1') {
            return $this->first_report($paramData['country_id']);
        } else if ($paramData['report_type'] == '2') {
            return $this->second_report($paramData['program_type_id']);
        } else if ($paramData['report_type'] == '3') {
            return $this->third_report($paramData['nomination_meeting_id']);
        } else if ($paramData['report_type'] == '4') {
            return $this->fourth_report($paramData['letter_to_mof']);
        }
    }


    public function first_report($country_id)
    {
        //getting user data releted to id
        $agendaList = MeetingAgenda::where('country_id', $country_id)->get();
        return view('report.first_report', compact('agendaList'));
    }

    public function first_report_details($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('program_type.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $id . ')')->get();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $id)->orWhereNull('nomination_meeting_id')->get();

        $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', $id)->with('ministry')->get();
        return view('report.first_report_details', compact('agendas', 'applicantList', 'nMeetingAttendeeList'));
    }

    public function third_report($meeting_id)
    {

        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $meeting_id)->first();

        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $meeting_id . ')')->get();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $meeting_id)->orWhereNull('nomination_meeting_id')->get();

        $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', $meeting_id)->with('ministry')->get();

        $reportTemplate = ReportTemplate::where('id', '8')->first();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);
        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($meeting->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($meeting->meeting_number), $LetterBody);

        return view('report.second_report', compact('meeting', 'agendas', 'applicantList', 'nMeetingAttendeeList', 'LetterBody'));
    }

    public function fourth_report($nomination_meeting_id)
    {
        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $nomination_meeting_id)->firstOrFail();

        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $nomination_meeting_id . ')')->get();

        $agenda = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $nomination_meeting_id . ')')->first();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $nomination_meeting_id)->orWhereNull('nomination_meeting_id')->get();

        $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', $nomination_meeting_id)->with('ministry')->get();

        $reportTemplate = ReportTemplate::where('id', '9')->firstOrFail();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);
        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($meeting->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($meeting->meeting_number), $LetterBody);
        $LetterBody = str_replace("{चिठी पाउने मन्त्रालयको नाम}", ($agenda->source == 'अन्य') ? $agenda->source_other : $agenda->source, $LetterBody);

        return view('report.third_report', compact('meeting', 'agendas', 'applicantList', 'nMeetingAttendeeList', 'LetterBody'));
    }

    public function second_report($type)
    {
        //getting user data releted to id
        $agendaList = MeetingAgenda::where('type', $type)->get();
        return view('report.fourth_report', compact('agendaList'));
    }

    public function prameter()
    {
        //removing staff_id from session
        if (request()->session()->has('basic_params')) {
            request()->session()->forget('basic_params');
            request()->session()->save();
        }

        $pTypes = ProgramType::all();
        $countries = Country::all();
        $meetings = Meeting::all();
        $ministries = Ministry::all();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();
        $staffServices = StaffService::orderBy('staff_service', 'asc')->get();
        $staffLevels = StaffLevel::all();
        $staffGroups = StaffGroup::all();
        $staffSubGroups = StaffSubGroup::all();

        return view('report.report_param', compact('countries', 'pTypes', 'meetings', 'ministries', 'staffPosts', 'staffServices', 'staffLevels', 'staffGroups', 'staffSubGroups'));
    }

    public function set_parameters(Request $request)
    {
        // dd($request->all());

        //validation data
        $validatedRequest = $this->validate(
            $request,
            [
                'report_base' => ['required', 'array', 'min:1'],
                'report_base.*' => ['required', 'numeric', 'between:1,10'],
                'country' => ['nullable', 'numeric', 'min:1'],
                'pType' => ['nullable', 'numeric', 'min:1'],
                'meeting' => ['nullable', 'numeric', 'min:1'],
                'ministry' => ['nullable', 'numeric', 'min:1'],
                'post' => ['nullable', 'numeric', 'min:1'],
                'service' => ['nullable', 'numeric', 'min:1'],
                'level' => ['nullable', 'numeric', 'min:1'],
                'group' => ['nullable', 'numeric', 'min:1'],
                'sub_group' => ['nullable', 'numeric', 'min:1'],
                'sanket_no' => ['nullable', 'string', 'max:255'],
            ]
        );


        if (empty($request->country) && empty($request->pType) && empty($request->meeting) && empty($request->ministry) && empty($request->post) && empty($request->service) && empty($request->level) && empty($request->group) && empty($request->sub_group) && empty($request->sanket_no)) {
            return back()->withError('Please select at least one parameter.')->withInput();
        }

        $basics = Applicant::with('meetingAgenda');

        if ($request->country) {
            $countryID = sanitize($request->country);
            $basics = $basics->whereHas('meetingAgenda', function ($q) use ($countryID) {
                $q->where('country_id', $countryID);
            });
        }

        if ($request->pType) {
            $pType = sanitize($request->pType);
            $basics = $basics->whereHas('meetingAgenda', function ($q) use ($pType) {
                $q->where('type', $pType);
            });
        }

        if ($request->meeting) {
            $meeting = sanitize($request->meeting);
            $basics = $basics->whereHas('meetingAgenda', function ($q) use ($meeting) {
                $q->where('meeting_id', $meeting);
            });
        }

        if ($request->ministry) {
            $basics = $basics->with('ministry')->where('ministry_id', sanitize($request->ministry));
        }

        if ($request->post) {
            $basics = $basics->with('staffPost')->where('post', sanitize($request->post));
        }

        if ($request->service) {
            $basics = $basics->with('staffService')->where('service', sanitize($request->service));
        }

        if ($request->level) {
            $basics = $basics->with('staffLevel')->where('level', sanitize($request->level));
        }

        if ($request->group) {
            $basics = $basics->with('staffGroup')->where('group', sanitize($request->group));
        }

        if ($request->sub_group) {
            $basics = $basics->with('staffSubGroup')->where('sub_group', sanitize($request->sub_group));
        }

        if ($request->sanket_no) {
            $basics = $basics->where('sanket_no', sanitize($request->sanket_no));
        }

        $finalbasics = $basics->get();

        //setting up basic_params into session
        if (!session()->has('basic_params')) {
            session()->put('basic_params', $finalbasics);
        }

        return redirect(make_route('report.basic_report'));
    }

    public function basic_report()
    {
        $title = 'Basic report';

        return view('report.basic_report', ['basic_reports' => session('basic_params')]);
    }

    public function leave_prameter()
    {
        //removing staff_id from session
        if (request()->session()->has('leave_params')) {
            request()->session()->forget('leave_params');
            request()->session()->save();
        }

        $countries = Country::all();
        $pTypes = ProgramType::all();
        $ministries = Ministry::all();
        $fys = FiscalYear::all();

        return view('report.study_leave_report.leave_report_param', compact('countries', 'pTypes', 'ministries', 'fys'));
    }

    public function set_leave_parameters(Request $request)
    {
        //validation data
        $validatedRequest = $this->validate(
            $request,
            [
                'report_base' => ['required', 'array', 'min:1'],
                'report_base.*' => ['required', 'numeric', 'between:1,10'],
                'country' => ['nullable', 'numeric', 'min:1'],
                'pType' => ['nullable', 'numeric', 'min:1'],
                'ministry' => ['nullable', 'numeric', 'min:1'],
                'fiscal_year' => ['nullable', 'numeric', 'min:1'],
                'sanket_no' => ['nullable', 'string', 'max:255'],
                'start_date' => ['nullable', 'string', 'max:10'],
                'end_date' => ['nullable', 'string', 'max:10'],
            ]
        );

        // dd($request);

        if (empty($request->country) && empty($request->pType) && empty($request->ministry) && empty($request->fiscal_year) && empty($request->sanket_no) && empty($request->start_date) && empty($request->end_date)) {
            return back()->withError('Please select at least one parameter.')->withInput();
        }

        $basics = StudyLeave::query();

        if ($request->country) {
            $basics = $basics->with('country')->where('country_id', sanitize($request->country));
        }

        if ($request->pType) {
            $basics = $basics->where('program_type', sanitize($request->pType));
        }

        if ($request->start_date && $request->end_date) {
            $start = ($request->start_date) ? $request->start_date : '';
            $end = ($request->end_date) ? $request->end_date : '';
            $basics = $basics->where('start_date', '>=', sanitize($start))
                ->where('return_date', '<=', sanitize($end));
        }

        if ($request->fiscal_year) {
            $fy = FiscalYear::where('id', sanitize($request->fiscal_year))->firstOrFail();
            $basics = $basics->whereBetween('approval_date', [$fy->start_date, $fy->end_date]);
        }

        if ($request->ministry) {
            $basics = $basics->with('ministry')->where('ministry_id', sanitize($request->ministry));
        }

        if ($request->sanket_no) {
            $basics = $basics->where('sanket_no', sanitize($request->sanket_no));
        }

        $finalbasics = $basics->get();

        //setting up leave_params into session
        if (!session()->has('leave_params')) {
            session()->put('leave_params', $finalbasics);
        }

        return redirect(make_route('report.leave_report'));
    }

    public function leave_report()
    {
        $title = 'Basic report';

        return view('report.study_leave_report.leave_report', ['leave_reports' => session('leave_params')]);
    }
}
