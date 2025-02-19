<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Ministry;
use App\Models\StaffPost;
use Illuminate\Http\Request;
use App\Models\MeetingAttendee;
use App\Http\Controllers\Controller;

class MeetingAttendeeController extends Controller
{
    public function index(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //for setting session
        if (!session()->has('meeting_id')) {
            session()->put('meeting_id', $id);
            session()->save();
        }

        //for unsetting session
        if (request()->session()->has('meeting_agenda_id')) {
            request()->session()->forget('meeting_agenda_id');
            request()->session()->save();
        }

        //for serch of attendee

        $searched = false;

        if (isset($request->s_tk) && isset($request->participant_name)) {
            $this->validate($request, [
                'participant_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'participant_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->participant_name);
        }

        $meetingAttendeeList = MeetingAttendee::query();

        if ($searched) {
            $meetingAttendeeList = $meetingAttendeeList->where('participant_name', 'like', '%' . $search_by . '%');
        }

        $meetingAttendeeList = $meetingAttendeeList->where('meeting_id', session()->get('meeting_id'))->with('ministry', 'post')->orderBy('post_order', 'asc')->paginate(50);

        // $meetingAttendeeList = MeetingAttendee::where('meeting_id', session()->get('meeting_id'))->with('ministry', 'post')->orderBy('post_order', 'desc')->get();

        //is first meeting
        $isFirst = Meeting::where('id', '<', $id)->count();

        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();
        $ministries = Ministry::all();

        return view('meeting_attendee.index', compact('meetingAttendeeList', 'isFirst', 'staffPosts', 'ministries'));
    }

    public function pull(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting_attendee.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //previous meeting id
        $prevMeeting = Meeting::where('id', '<', $id)->orderByDesc('id')->first();

        $meetingid = $prevMeeting->id;

        //previous meetings
        $oldAttendees = MeetingAttendee::where('meeting_id', $meetingid)->get();

        $dataAdded = false;

        // loop through the array
        foreach ($oldAttendees as $attendee) {
            $newAttendee = new MeetingAttendee();
            $newAttendee->meeting_id = session('meeting_id');
            $newAttendee->participant_post = $attendee->participant_post;
            $newAttendee->participant_name = $attendee->participant_name;
            $newAttendee->ministry_id = $attendee->ministry_id;
            $newAttendee->meeting_post = $attendee->meeting_post;
            $newAttendee->post_order = $attendee->post_order;
            $newAttendee->participation_type = $attendee->participation_type;

            if ($newAttendee->save()) {
                $dataAdded = true;
            }
        }

        if ($dataAdded) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        } else {
            return back()->withError('अघिल्लो बैठकमा कुनै पनि पदाधिकारीहरू फेला परेनन्।');
        }
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'meeting_id' => ['required', 'integer', 'max:255'],
            'participant_post' => ['required', 'string', 'max:255'],
            'participant_name' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'meeting_post' => ['nullable', 'string', 'max:255'],
            'post_order' => ['required', 'integer', 'min:1'],
            'participation_type' => ['required', 'in:0,1']
        ], [
            'meeting_id.required' => 'वैठक राख्नुहोस्',
            'participant_post.required' => 'पद छान्नुहोस्',
            'participant_name.required' => 'नाम राख्नुहोस्',
            'ministry_id.required' => 'मन्त्रालय/निकाय छान्नुहोस्',
            'post_order.required' => 'पद क्र.सं. राख्नुहोस्',
            'participation_type.required' => 'उपस्थितिको प्रकार छान्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();
        // dd($validatedData);
        $addMeetingAttendee = new MeetingAttendee();
        if ($addMeetingAttendee->create($validatedData)) {
            return redirect()->route('meeting_attendee.index', [session('meeting_id'), hashtag(session('meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('meeting_attendee.index', [session('meeting_id'), hashtag(session('meeting_id'))])->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting_attendee.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meetingAttendee data releted to id
        $meetingAttendeeInfo = MeetingAttendee::where('id', $id)->firstOrFail();

        $ministries = Ministry::all();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();

        return view('meeting_attendee.edit', compact('meetingAttendeeInfo', 'ministries', 'staffPosts'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'participant_post' => ['required', 'string', 'max:255'],
            'participant_name' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'meeting_post' => ['nullable', 'string', 'max:255'],
            'post_order' => ['required', 'integer', 'min:1'],
            'participation_type' => ['required', 'in:0,1']
        ], [
            'participant_post.required' => 'पद छान्नुहोस्',
            'participant_name.required' => 'नाम राख्नुहोस्',
            'ministry_id.required' => 'मन्त्रालय/निकाय छान्नुहोस्',
            'post_order.required' => 'पद क्र.सं. राख्नुहोस्',
            'participation_type.required' => 'उपस्थितिको प्रकार छान्नुहोस्',
        ]);

        //update data
        $editmeetingAttendee = MeetingAttendee::where('id', $id)->firstOrFail();

        $oldData = $editmeetingAttendee->getOriginal();

        //return to index page if updated
        if ($editmeetingAttendee->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editmeetingAttendee);

            if ($updatedJson)
                $this->actionlog('App\Model\MeetingAttendee', $id, $updatedJson);

            return redirect()->route('meeting_attendee.index', [session()->get('meeting_id'), hashtag(session()->get('meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting ministry data releted to id
        $ministry = MeetingAttendee::where('id', $id)->firstOrFail();

        if ($ministry->delete()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
        }
        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }
}
