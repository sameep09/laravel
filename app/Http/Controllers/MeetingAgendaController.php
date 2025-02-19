<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Delete;
use App\Models\Country;
use App\Models\ProgramType;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Http\Requests\MeetingAttendeeRequest;

class MeetingAgendaController extends Controller
{
    public function agenda_index(Request $request, $id, $hashtag)
    {

        // dd($request);
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

        //for serch of agenda

        $searched_agenda = false;

        if (isset($request->s_tk) && isset($request->subject)) {
            $this->validate($request, [
                'subject' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'subject.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched_agenda = true;
            $search_by = sanitize($request->subject);
        }

        $meetingAgendaList = MeetingAgenda::query()->with('country', 'program_type')->where('meeting_id', session()->get('meeting_id'));

        if ($searched_agenda) {
            $meetingAgendaList = $meetingAgendaList->where('subject', 'like', '%' . $search_by . '%');
        }

        $meetingAgendaList = $meetingAgendaList->orderBy('id', 'desc')->paginate(50);

        // $meetingAgendaList = MeetingAgenda::query()->with('country', 'program_type')->where('meeting_id', session()->get('meeting_id'))->orderBy('id', 'desc')->get();

        $countries = Country::all();
        $ptypes = ProgramType::all();

        return view('meeting_agenda.agenda-index', compact('meetingAgendaList', 'countries', 'ptypes'));
    }

    public function store_agenda(MeetingAttendeeRequest $request)
    {
        $validatedData = $request->validated();

        $date_type = sanitize($validatedData['date_type']);
        if ($date_type === 'AD') {
            $start_date_ad = sanitize($validatedData['start_date_ad']);
            $validatedData['start_date'] = Date::engToNep($start_date_ad);

            $end_date_ad = sanitize($validatedData['end_date_ad']);
            $validatedData['end_date'] = Date::engToNep($end_date_ad);

            $form_deadline_ad = sanitize($validatedData['form_deadline_ad']);
            $validatedData['form_deadline'] = Date::engToNep($form_deadline_ad);
        } elseif ($date_type === 'BS') {
            $start_date = sanitize($validatedData['start_date']);
            $validatedData['start_date_ad'] = Date::nepToEng($start_date);

            $end_date = sanitize($validatedData['end_date']);
            $validatedData['end_date_ad'] = Date::nepToEng($end_date);

            $form_deadline = sanitize($validatedData['form_deadline']);
            $validatedData['form_deadline_ad'] = Date::nepToEng($form_deadline);
        }

        if ($request->file('source_document')) {
            $uploadFile = $request->source_document;
            $file_ext = $uploadFile->getClientOriginalExtension();

            $newFile = 'source-document-' . $request->country_id . '-' . date('Y-m-d') . '-' . time() . '-' . RandomNum(5) . '.' . $file_ext;

            $file_name = $uploadFile->storeAs('uploads/source-document', $newFile, 'public');
        }

        $validatedData['source_document'] = $file_name;
        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addMeetingAgenda = new MeetingAgenda();
        if ($addMeetingAgenda->create($validatedData)) {
            return redirect()->route('meeting_agenda.agenda_index', [session('meeting_id'), hashtag(session('meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('meeting_agenda.agenda_index', [session('meeting_id'), hashtag(session('meeting_id'))])->withError('तथ्यांक थप्न सकिएन।');
    }


    public function edit_agenda($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('meeting_agenda.agenda-index', [session()->get('meeting_id'), hashtag(session()->get('meeting_id'))])->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meetingAgenda data releted to id
        $meetingAgendaInfo = MeetingAgenda::where('id', $id)->firstOrFail();

        $countries = Country::all();
        $ptypes = ProgramType::all();

        return view('meeting_agenda.edit-agenda', compact('meetingAgendaInfo', 'countries', 'ptypes'));
    }

    public function update_agenda(MeetingAttendeeRequest $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data

        $validatedData = $request->validated();

        $date_type = sanitize($validatedData['date_type']);
        if ($date_type === 'AD') {
            $start_date_ad = sanitize($validatedData['start_date_ad']);
            $validatedData['start_date'] = Date::engToNep($start_date_ad);

            $end_date_ad = sanitize($validatedData['end_date_ad']);
            $validatedData['end_date'] = Date::engToNep($end_date_ad);

            $form_deadline_ad = sanitize($validatedData['form_deadline_ad']);
            $validatedData['form_deadline'] = Date::engToNep($form_deadline_ad);
        } elseif ($date_type === 'BS') {
            $start_date = sanitize($validatedData['start_date']);
            $validatedData['start_date_ad'] = Date::nepToEng($start_date);

            $end_date = sanitize($validatedData['end_date']);
            $validatedData['end_date_ad'] = Date::nepToEng($end_date);

            $form_deadline = sanitize($validatedData['form_deadline']);
            $validatedData['form_deadline_ad'] = Date::nepToEng($form_deadline);
        }

        if ($request->hasFile('source_document')) {
            $uploadFile = $request->source_document;
            $file_ext = $uploadFile->getClientOriginalExtension();

            $newFile = 'source-document-' . $request->country_id . '-' . date('Y-m-d') . '-' . time() . '-' . RandomNum(5) . '.' . $file_ext;

            $file_name = $uploadFile->storeAs('uploads/source-document', $newFile, 'public');
        }

        //update data
        $editMeetingAgenda = MeetingAgenda::where('id', $id)->firstOrFail();

        if (empty($file_name)) {
            $file_name = $editMeetingAgenda->source_document;
        }

        $validatedData['source_document'] = $file_name;

        $oldData = $editMeetingAgenda->getOriginal();

        //return to index page if updated
        if ($editMeetingAgenda->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editMeetingAgenda);

            if ($updatedJson)
                $this->actionlog('App\Model\MeetingAgenda', $id, $updatedJson);

            return redirect()->route('meeting_agenda.agenda_index', [session()->get('meeting_id'), hashtag(session()->get('meeting_id'))])->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन। ')->withInput();
    }

    public function delete_agenda(Request $request)
    {
        $id = sanitize($request->id);
        $hashtag = sanitize($request->hashtag);

        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।');
        }

        //getting meetingAgenda data releted to id
        $meetingAgenda = MeetingAgenda::where('id', $id)->firstOrFail();

        $relations = ['agenda_ministry', 'applicants', 'applicants_main', 'applicants_sub', 'applicants_no', 'applicants_yes', 'applicants_final_main', 'applicants_final_sub', 'applicants_final_no', 'applicants_final_yes'];

        if (Delete::check($meetingAgenda, $relations))
            if ($meetingAgenda->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
