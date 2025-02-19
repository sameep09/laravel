<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Applicant;
use App\Models\StaffPost;
use App\Models\StaffGroup;
use App\Models\StaffLevel;
use App\Models\StaffService;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Models\StaffSubGroup;
use App\Models\AgendaMinistry;
use App\Models\NominationMeeting;

class ApplicantController extends Controller
{
    public function index(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //for setting session
        if (!session()->has('agenda_ministry_id')) {
            session()->put('agenda_ministry_id', $id);
            session()->save();
        }

        $agenda = AgendaMinistry::where('id', session()->get('agenda_ministry_id'))->firstOrFail();

        $searched = false;

        if (isset($request->s_tk) && isset($request->applicant_name)) {
            $this->validate($request, [
                'applicant_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'applicant_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->applicant_name);
        }

        $applicantList = Applicant::query()->where('agenda_ministry_id', session()->get('agenda_ministry_id'))->with('agendaMinistry', 'meetingAgenda', 'nominationMeeting', 'ministry', 'staffPost', 'staffService', 'staffGroup', 'staffSubGroup', 'staffLevel');

        if ($searched) {
            $applicantList = $applicantList->where('full_name', 'like', '%' . $search_by . '%');
        }

        $applicantList = $applicantList->orderBy('id', 'desc')->paginate(50);

        $agendaMinistryList = AgendaMinistry::all();
        $meetingAgendaList = MeetingAgenda::all();
        $nMeetingList = NominationMeeting::all();
        $ministries = Ministry::all();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();
        $staffServices = StaffService::orderBy('id', 'asc')->get();
        $staffGroups = StaffGroup::orderBy('id', 'asc')->get();
        $staffSubGroups = StaffSubGroup::orderBy('id', 'asc')->get();
        $staffLevels = StaffLevel::orderBy('id', 'asc')->get();

        return view('applicant.index', compact('applicantList', 'agendaMinistryList', 'meetingAgendaList', 'ministries', 'nMeetingList', 'agenda', 'staffPosts', 'staffServices', 'staffGroups', 'staffSubGroups', 'staffLevels'));
    }


    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'agenda_ministry_id' => ['required', 'string', 'max:255'],
            'meeting_agenda_id' => ['required', 'string', 'max:255'],
            'nomination_meeting_id' => ['nullable', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'sanket_no' => ['required', 'string', 'max:255'],
            'post' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'initial_appointment_date' => ['required', 'string', 'max:255'],
            'current_post_appointment_date' => ['required', 'string', 'max:255'],
            'current_office' => ['required', 'string', 'max:255'],
            'service' => ['required', 'integer', 'min:1'],
            'group' => ['required', 'integer', 'min:1'],
            'sub_group' => ['nullable', 'integer', 'min:1'],
            'level' => ['required', 'integer', 'min:1'],
            'samayojan_level' => ['required', 'integer', 'min:1'],
            'intended_subject' => ['nullable', 'string', 'max:255'],
            'intended_institution' => ['nullable', 'string', 'max:255'],
            'current_academic_level' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'mobile_no' => ['required', 'string', 'max:255'],
        ], [
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'post.required' => 'पद छान्नुहोस्',
            'full_name.required' => 'पुरा नाम राख्नुहोस्',
            'initial_appointment_date.required' => 'सुरु नियुक्ति मिति राख्नुहोस्',
            'current_post_appointment_date.required' => 'हालको पदमा नियुक्ति मिति राख्नुहोस्',
            'current_office.required' => 'हालको कार्यालय राख्नुहोस्',
            'service.required' => 'सेवा छान्नुहोस्',
            'group.required' => 'समूह छान्नुहोस्',
            'level.required' => 'श्रेणी/तह  छान्नुहोस्',
            'samayojan_level.required' => 'समायोजन भएको तह छान्नुहोस्',
            'current_academic_level.required' => 'हालको शैक्षिक योग्यता राख्नुहोस्',
            'email.required' => 'ईमेल राख्नुहोस्',
            'mobile_no.required' => 'सम्पर्क राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();
        $addApplicant = new Applicant();
        if ($addApplicant->create($validatedData)) {
            return redirect()->route('applicant.index', [session('agenda_ministry_id'), hashtag(session('agenda_ministry_id'))])->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('applicant.index', [session('agenda_ministry_id'), hashtag(session('agenda_ministry_id'))])->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('applicant.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting applicant data releted to id
        $applicantInfo = Applicant::where('id', $id)->firstOrFail();

        $agendaMinistryList = AgendaMinistry::all();
        $meetingAgendaList = MeetingAgenda::all();
        $nMeetingList = NominationMeeting::all();
        $ministries = Ministry::all();
        $staffPosts = StaffPost::orderBy('post_order', 'asc')->get();
        $staffServices = StaffService::orderBy('id', 'asc')->get();
        $staffGroups = StaffGroup::orderBy('id', 'asc')->get();
        $staffSubGroups = StaffSubGroup::orderBy('id', 'asc')->get();
        $staffLevels = StaffLevel::orderBy('id', 'asc')->get();

        return view('applicant.edit', compact('applicantInfo', 'agendaMinistryList', 'meetingAgendaList', 'ministries', 'nMeetingList', 'staffPosts', 'staffServices', 'staffGroups', 'staffSubGroups', 'staffLevels'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'agenda_ministry_id' => ['required', 'string', 'max:255'],
            'meeting_agenda_id' => ['required', 'string', 'max:255'],
            'nomination_meeting_id' => ['nullable', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'sanket_no' => ['required', 'string', 'max:255'],
            'post' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'initial_appointment_date' => ['required', 'string', 'max:255'],
            'current_post_appointment_date' => ['required', 'string', 'max:255'],
            'current_office' => ['required', 'string', 'max:255'],
            'service' => ['required', 'integer', 'min:1'],
            'group' => ['required', 'integer', 'min:1'],
            'sub_group' => ['nullable', 'integer', 'min:1'],
            'level' => ['required', 'integer', 'min:1'],
            'samayojan_level' => ['required', 'integer', 'min:1'],
            'intended_subject' => ['nullable', 'string', 'max:255'],
            'intended_institution' => ['nullable', 'string', 'max:255'],
            'current_academic_level' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
            'mobile_no' => ['required', 'string', 'max:255'],
            'is_nominated' => ['required', 'in:0,1,2,3'],
            'is_selected' => ['required', 'in:0,1,2,3'],
        ], [
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'post.required' => 'पद छान्नुहोस्',
            'full_name.required' => 'पुरा नाम राख्नुहोस्',
            'initial_appointment_date.required' => 'सुरु नियुक्ति मिति राख्नुहोस्',
            'current_post_appointment_date.required' => 'हालको पदमा नियुक्ति मिति राख्नुहोस्',
            'current_office.required' => 'हालको कार्यालय राख्नुहोस्',
            'service.required' => 'सेवा छान्नुहोस्',
            'group.required' => 'समूह छान्नुहोस्',
            'level.required' => 'श्रेणी/तह  छान्नुहोस्',
            'samayojan_level.required' => 'समायोजन भएको तह छान्नुहोस्',
            'current_academic_level.required' => 'हालको शैक्षिक योग्यता राख्नुहोस्',
            'email.required' => 'ईमेल राख्नुहोस्',
            'mobile_no.required' => 'सम्पर्क राख्नुहोस्',
        ]);

        //update data
        $editApplicant = Applicant::where('id', $id)->firstOrFail();

        $oldData = $editApplicant->getOriginal();

        //return to index page if updated
        if ($editApplicant->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editApplicant);

            if ($updatedJson)
                $this->actionlog('App\Model\Applicant', $id, $updatedJson);

            return redirect()->route('applicant.index', [session()->get('agenda_ministry_id'), hashtag(session()->get('agenda_ministry_id'))])->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन।')->withInput();
    }

    public function delete(Request $request)
    {
        $id = sanitize($request->id);
        $hashtag = sanitize($request->hashtag);

        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।');
        }

        //getting applicant data releted to id
        $applicant = Applicant::where('id', $id)->firstOrFail();

        if ($applicant->delete()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
        }
        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }
}
