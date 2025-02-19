<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\StaffPost;
use Illuminate\Http\Request;
use App\Models\NominationMeeting;
use App\Models\NominationMeetingAttendee;

class NominationMeetingAttendeeController extends Controller
{
    public function index(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //for setting session
        if (!session()->has('nomination_meeting_id')) {
            session()->put('nomination_meeting_id', $id);
            session()->save();
        }

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

        $nMeetingAttendeeList = NominationMeetingAttendee::query()->with('ministry', 'post')->where('nomination_meeting_id', session()->get('nomination_meeting_id'));

        if ($searched) {
            $nMeetingAttendeeList = $nMeetingAttendeeList->where('participant_name', 'like', '%' . $search_by . '%');
        }

        $nMeetingAttendeeList = $nMeetingAttendeeList->orderBy('post_order', 'asc')->paginate(50);

        // $nMeetingAttendeeList = NominationMeetingAttendee::where('nomination_meeting_id', session()->get('nomination_meeting_id'))->with('ministry', 'post')->orderBy('id', 'desc')->get();

        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();
        $ministries = Ministry::all();

        //is first meeting
        $isFirst = NominationMeeting::where('id', '<', $id)->count();

        return view('nomination_meeting_attendee.index', compact('nMeetingAttendeeList', 'ministries', 'staffPosts', 'isFirst'));
    }

    public function pull(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting_attendee.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //previous meeting id
        $prevMeeting = NominationMeeting::where('id', '<', $id)->orderByDesc('id')->first();

        $prevMeetingid = $prevMeeting->id;

        //previous meetings
        $oldAttendees = NominationMeetingAttendee::where('nomination_meeting_id', $prevMeetingid)->get();

        $dataAdded = false;

        // loop through the array
        foreach ($oldAttendees as $attendee) {
            $newAttendee = new NominationMeetingAttendee();
            $newAttendee->nomination_meeting_id = session('nomination_meeting_id');
            $newAttendee->participant_post = $attendee->participant_post;
            $newAttendee->participant_name = $attendee->participant_name;
            $newAttendee->ministry_id = $attendee->ministry_id;
            $newAttendee->post_order = $attendee->post_order;
            $newAttendee->meeting_post = $attendee->meeting_post;
            $newAttendee->participation_type = $attendee->participation_type;

            if ($newAttendee->save()) {
                $dataAdded = true;
            }
        }

        if ($dataAdded) {
            return back()->withSuccess('अघिल्लो बैठकमाका पदाधिकारीहरू सफलतापूर्वक तानियो।');
        } else {
            return back()->withError('अघिल्लो बैठकमा कुनै पनि पदाधिकारीहरू फेला परेनन्।');
        }
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'nomination_meeting_id' => ['required', 'integer', 'max:255'],
            'participant_post' => ['required', 'string', 'max:255'],
            'participant_name' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'post_order' => ['required', 'integer', 'min:1'],
            'meeting_post' => ['nullable', 'string', 'max:255'],
            'participation_type' => ['required', 'in:0,1']
        ], [
            'participant_post.required' => 'पद छान्नुहोस्',
            'participant_name.required' => 'नाम राख्नुहोस्',
            'ministry_id.required' => 'मन्त्रालय/निकाय छान्नुहोस्',
            'post_order.required' => 'पद क्र.सं. राख्नुहोस्',
            'participation_type.required' => 'उपस्थितिको प्रकार छान्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addMeetingAttendee = new NominationMeetingAttendee();
        if ($addMeetingAttendee->create($validatedData)) {
            return redirect()->route('nomination_meeting_attendee.index', [session('nomination_meeting_id'), hashtag(session('nomination_meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('nomination_meeting_attendee.index', [session('nomination_meeting_id'), hashtag(session('nomination_meeting_id'))])->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('nomination_meeting_attendee.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meetingAttendee data releted to id
        $meetingAttendeeInfo = NominationMeetingAttendee::with('post')->where('id', $id)->firstOrFail();

        $ministries = Ministry::all();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();

        return view('nomination_meeting_attendee.edit', compact('meetingAttendeeInfo', 'ministries', 'staffPosts'));
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
            'post_order' => ['required', 'integer', 'min:1'],
            'meeting_post' => ['nullable', 'string', 'max:255'],
            'participation_type' => ['required', 'in:0,1']
        ]);

        //update data
        $editNominationMeetingAttendee = NominationMeetingAttendee::where('id', $id)->firstOrFail();

        $oldData = $editNominationMeetingAttendee->getOriginal();

        //return to index page if updated
        if ($editNominationMeetingAttendee->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editNominationMeetingAttendee);

            if ($updatedJson)
                $this->actionlog('App\Model\NominationMeetingAttendee', $id, $updatedJson);

            return redirect()->route('nomination_meeting_attendee.index', [session()->get('nomination_meeting_id'), hashtag(session()->get('nomination_meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting Nomination Meeting Attendee data releted to id
        $nMeetingAttendee = NominationMeetingAttendee::where('id', $id)->firstOrFail();

        if ($nMeetingAttendee->delete()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
        }
        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }
}
