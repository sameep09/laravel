<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Delete;
use App\Models\Meeting;
use App\Models\StaffPost;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Models\AgendaMinistry;
use App\Models\ReportTemplate;
use App\Models\MeetingAttendee;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        //for unsetting session
        if (request()->session()->has('meeting_id')) {
            request()->session()->forget('meeting_id');
            request()->session()->save();
        }

        $searched = false;

        if (isset($request->s_tk) && isset($request->meeting)) {
            $this->validate($request, [
                'meeting' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'meeting.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->meeting);
        }

        $meetingList = Meeting::query();

        if ($searched) {
            $meetingList = $meetingList->where('meeting_number', 'like', '%' . $search_by . '%');
        }

        $meetingList = $meetingList->orderBy('id', 'desc')->paginate(50);

        // $meetingList = Meeting::with('post')->orderBy('id', 'desc')->get();

        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();

        return view('meeting.index', compact('meetingList', 'staffPosts'));
    }

    public function meeting_report($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meetingInfo = Meeting::with('post')->where('id', $id)->firstOrFail();

        $meetingAttendeeList = MeetingAttendee::where('meeting_id', $id)->with('ministry')->orderBy('post_order', 'asc')->get();

        $agendaList = MeetingAgenda::query()
            ->with('country', 'program_type', 'agenda_ministry')
            ->where('meeting_id', $id)
            ->get();

        $agendaMinistryList = AgendaMinistry::with('meeting', 'ministry', 'meetingAgenda', 'nominationMeeting')
            ->where('meeting_id', $id)
            ->where('agenda_id', $id)
            ->get();

        $reportTemplate = ReportTemplate::where('id', '1')->firstOrFail();
        $subBody = ReportTemplate::where('id', '2')->firstOrFail();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);
        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($meetingInfo->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($meetingInfo->meeting_number), $LetterBody);
        $LetterBody = str_replace("{बैठक समय}", EngToUTF8($meetingInfo->meeting_time), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको नाम}", EngToUTF8($meetingInfo->chaired_by), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको पद}", EngToUTF8($meetingInfo->post->staff_post), $LetterBody);

        return view('meeting.meeting-report', compact('meetingInfo', 'meetingAttendeeList', 'agendaList', 'LetterBody', 'subBody'));
    }

    public function store_report(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'meeting_report' => ['required', 'string'],
        ], [
            'meeting_report.required' => 'छात्रवृत्ति समितिको बैठकका निर्णयहरु राख्नुहोस्',
        ]);

        //update data
        $editMeeting = Meeting::where('id', $id)->firstOrFail();

        $oldData = $editMeeting->getOriginal();

        //return to index page if updated
        if ($editMeeting->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editMeeting);

            if ($updatedJson)
                $this->actionlog('App\Model\Meeting', $id, $updatedJson);

            return back()->withSuccess('छात्रवृत्ति समितिको बैठकका निर्णयहरु सम्पादन गरियो।');
        }
        return back()->withError('छात्रवृत्ति समितिको बैठकका निर्णयहरु सम्पादन गर्न सकिएन। ')->withInput();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'meeting_number' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'string', 'max:255'],
            'meeting_time' => ['required', 'string', 'max:255'],
            'chaired_by' => ['required', 'string', 'max:255'],
            'chaired_by_post' => ['required', 'string', 'max:255']
        ], [
            'meeting_number.required' => 'वैठक नम्बर राख्नुहोस्',
            'meeting_date.required' => 'वैठक मिति राख्नुहोस्',
            'meeting_time.required' => 'समय राख्नुहोस्',
            'chaired_by.required' => 'अध्यक्षता गर्ने पदाधिकारीको नाम राख्नुहोस्',
            'chaired_by_post.required' => 'अध्यक्षता गर्ने पदाधिकारीको पद छान्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addMeeting = new Meeting();
        if ($addMeeting->create($validatedData)) {
            return redirect()->route('meeting.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('meeting.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meetingInfo = Meeting::where('id', $id)->firstOrFail();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();

        return view('meeting.edit', compact('meetingInfo', 'staffPosts'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'meeting_number' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'string', 'max:255'],
            'meeting_time' => ['required', 'string', 'max:255'],
            'chaired_by' => ['required', 'string', 'max:255'],
            'chaired_by_post' => ['required', 'string', 'max:255'],
        ], [
            'meeting_number.required' => 'वैठक नम्बर राख्नुहोस्',
            'meeting_date.required' => 'वैठक मिति राख्नुहोस्',
            'meeting_time.required' => 'समय राख्नुहोस्',
            'chaired_by.required' => 'अध्यक्षता गर्ने पदाधिकारीको नाम राख्नुहोस्',
            'chaired_by_post.required' => 'अध्यक्षता गर्ने पदाधिकारीको पद छान्नुहोस्',
        ]);

        //update data
        $editMeeting = Meeting::where('id', $id)->firstOrFail();

        $oldData = $editMeeting->getOriginal();

        //return to index page if updated
        if ($editMeeting->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editMeeting);

            if ($updatedJson)
                $this->actionlog('App\Model\Meeting', $id, $updatedJson);

            return redirect()->route('meeting.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन। ')->withInput();
    }

    public function delete($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meeting data releted to id
        $meeting = Meeting::where('id', $id)->firstOrFail();

        $relations = ['meeting_attendee', 'meeting_agenda', 'agenda_ministry'];

        if (Delete::check($meeting, $relations))
            if ($meeting->delete()) {
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
            }
        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }

    public function update_status(Request $request)
    {
        $dataAdded = false;

        // dd($request->all());

        //validate data
        $validatedData = $request->validate([
            "meeting_id"    => ["required", "array", "min:1"],
            'completed_status' => ['required', "array", 'min:1'],
            'completed_status.*' => ['required', "numeric", 'in:0,1'],
        ]);

        $totalData = count($request->meeting_id);
        $countCompleted = count($request->completed_status);
        $meeting_id = $request->meeting_id;

        $pos = 0;

        foreach ($request->completed_status as $key => $completed_status) {
            $updateApplicant = Meeting::where('id', $meeting_id[$pos])->first();

            $updateApplicant->completed_status = $request->completed_status[$key];

            $oldData = $updateApplicant->getOriginal();

            //return to applicant index page if applicant is updated
            if ($updateApplicant->update()) {
                $updatedJson = get_updated_data($oldData, $updateApplicant);

                if ($updatedJson)
                    $this->actionlog('App\Model\Meeting', $meeting_id[$pos], $updatedJson);

                $dataAdded = true;
            }

            $pos++;
        }

        if ($dataAdded) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        } else {
            return back()->withError('तथ्यांक सम्पादन गर्न सकिएन।');
        }
    }
}
