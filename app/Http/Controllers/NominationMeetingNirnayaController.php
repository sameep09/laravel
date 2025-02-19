<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Applicant;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Models\AgendaMinistry;
use App\Models\ReportTemplate;
use App\Models\NominationMeeting;
use App\Models\NominationMeetingAttendee;

class NominationMeetingNirnayaController extends Controller
{

    public function index(Request $request)
    {
        //for unsetting session
        if (request()->session()->has('nomination_meeting_id')) {
            request()->session()->forget('nomination_meeting_id');
            request()->session()->save();
        }

        $searched = false;

        if (isset($request->s_tk) && isset($request->meeting_number)) {
            $this->validate($request, [
                'meeting_number' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'meeting_number.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->meeting_number);
        }

        $nMeetingList = NominationMeeting::query()->with('post');

        if ($searched) {
            $nMeetingList = $nMeetingList->where('meeting_number', $search_by);
        }

        $nMeetingList = $nMeetingList->orderBy('id', 'desc')->paginate(50);

        // $nMeetingList = NominationMeeting::with('post')->orderBy('id', 'desc')->get();

        return view('nomination_meeting_nirnaya.index', compact('nMeetingList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'meeting_date' => ['required', 'string', 'max:255'],
            'meeting_time' => ['required', 'string', 'max:255'],
            'chaired_by' => ['required', 'string', 'max:255'],
            'chaired_by_post' => ['required', 'string', 'max:255']
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($request);
        $addMeeting = new NominationMeeting();
        if ($addMeeting->create($validatedData)) {
            return redirect()->route('nomination_meeting_nirnaya.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('nomination_meeting_nirnaya.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meetingInfo = NominationMeeting::where('id', $id)->firstOrFail();

        return view('nomination_meeting.edit', compact('meetingInfo'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'meeting_date' => ['required', 'string', 'max:255'],
            'meeting_time' => ['required', 'string', 'max:255'],
            'chaired_by' => ['required', 'string', 'max:255'],
            'chaired_by_post' => ['required', 'string', 'max:255'],
        ]);

        //update data
        $editMeeting = NominationMeeting::where('id', $id)->firstOrFail();

        $oldData = $editMeeting->getOriginal();

        //return to index page if updated
        if ($editMeeting->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editMeeting);

            if ($updatedJson)
                $this->actionlog('App\Model\NominationMeeting', $id, $updatedJson);

            return redirect()->route('nomination_meeting.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन। ')->withInput();
    }

    public function delete(Request $request)
    {
        $id = sanitize($request->id);
        $hashtag = sanitize($request->hashtag);

        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $id)->firstOrFail();

        if ($meeting->delete()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
        }
        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }

    public function agenda($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //for setting session
        if (!session()->has('nomination_meeting_id')) {
            session()->put('nomination_meeting_id', $id);
            session()->save();
        }

        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $id)->firstOrFail();

        $agendaMinistryList = AgendaMinistry::all();
        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry', 'staffPost')->where('nomination_meeting_id', $id)->orWhereNull('nomination_meeting_id')->get();

        return view('nomination_meeting_nirnaya.agenda', compact('meeting', 'agendaMinistryList', 'applicantList'));
    }

    public function storeAgenda(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'nomination_meeting_id' => ['required', 'string', 'max:255'],
            'completed_status' => ['required', 'in:0,1'],
        ]);

        $agenda_ministry_id = sanitize($request->agenda_ministry_id);

        //update data
        $editMeeting = AgendaMinistry::where('id', $agenda_ministry_id)->firstOrFail();

        //return to meeting index page if meeting is updated
        if ($editMeeting->update($validatedData)) {
            return redirect()->route('nomination_meeting.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन।')->withInput();
    }

    public function update_status(Request $request)
    {
        $dataAdded = false;

        //validate data
        $validatedData = $request->validate([
            "meeting_id"    => ["required", "string", "min:1"],
            "applicant_id"    => ["required", "array", "min:1"],
            "applicant_id.*"  => ["required", "string", "distinct"],
            'is_selected' => ['required', "array", 'in:0,1,2,3'],
        ]);

        $totalData = count($request->applicant_id);
        $applicant_id = $request->applicant_id;
        $is_selected = $request->is_selected;
        $meeting_id = $request->meeting_id;

        for ($count = 0; $count < $totalData; $count++) {

            $updateApplicant = Applicant::where('id', $applicant_id[$count])->first();

            $updateApplicant->id = $applicant_id[$count];
            $updateApplicant->is_selected = $is_selected[$count];
            $updateApplicant->nomination_meeting_id = $meeting_id;

            $oldData = $updateApplicant->getOriginal();

            //return to applicant index page if applicant is updated
            if ($updateApplicant->update()) {
                $updatedJson = get_updated_data($oldData, $updateApplicant);

                if ($updatedJson)
                    $this->actionlog('App\Model\Applicant', $updateApplicant->id, $updatedJson);

                $dataAdded = true;
            }
        }

        if ($dataAdded) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        } else {
            return back()->withError('तथ्यांक सम्पादन गर्न सकिएन।');
        }
    }

    public function report_six($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting_nirnaya.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $id)->firstOrFail();

        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $id . ')')->get();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $id)->orWhereNull('nomination_meeting_id')->get();

        $subBody = ReportTemplate::where('id', '7')->firstOrFail();

        return view('nomination_meeting_nirnaya.report-six', compact('agendas', 'applicantList', 'subBody'));
    }

    public function report_six_all($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting_nirnaya.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $id . ')')->get();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $id)->orWhereNull('nomination_meeting_id')->get();

        $subBody = ReportTemplate::where('id', '7')->firstOrFail();

        return view('nomination_meeting_nirnaya.report-six-all', compact('agendas', 'applicantList', 'subBody'));
    }

    public function report_all($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting_nirnaya.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meeting = NominationMeeting::where('id', $id)->firstOrFail();

        $agendas = MeetingAgenda::whereRaw('id in (select meeting_agenda_id from applicants where nomination_meeting_id = ' . $id . ')')->get();

        $applicantList = Applicant::with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry')->where('nomination_meeting_id', $id)->orWhereNull('nomination_meeting_id')->get();

        $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', $id)->with('ministry')->get();

        $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', $id)->with('ministry')->get();

        $reportTemplate = ReportTemplate::where('id', '6')->firstOrFail();
        $subBody = ReportTemplate::where('id', '7')->firstOrFail();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);
        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($meeting->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($meeting->meeting_number), $LetterBody);
        $LetterBody = str_replace("{बैठक समय}", EngToUTF8($meeting->meeting_time), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको नाम}", EngToUTF8($meeting->chaired_by), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको पद}", EngToUTF8($meeting->post->staff_post), $LetterBody);

        return view('nomination_meeting_nirnaya.report-all', compact('meeting', 'agendas', 'applicantList', 'nMeetingAttendeeList', 'LetterBody', 'subBody'));
    }
}
