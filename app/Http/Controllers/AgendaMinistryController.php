<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Delete;
use App\Models\Meeting;
use App\Models\Ministry;
use Illuminate\Http\Request;
use App\Models\MeetingAgenda;
use App\Models\AgendaMinistry;
use App\Models\ReportTemplate;
use App\Models\NominationMeeting;

class AgendaMinistryController extends Controller
{
    public function index(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //for setting session
        if (!session()->has('meeting_agenda_id')) {
            session()->put('meeting_agenda_id', $id);
            session()->save();
        }

        //for unsetting session
        if (request()->session()->has('agenda_ministry_id')) {
            request()->session()->forget('agenda_ministry_id');
            request()->session()->save();
        }

        $searched = false;

        if (isset($request->s_tk) && isset($request->ministry)) {
            $this->validate($request, [
                'ministry' => ['required', 'integer'],
                's_tk' => ['required', 'string'],
            ], [
                'ministry.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->ministry);
        }

        $agendaMinistryList = AgendaMinistry::query()->with('meeting', 'ministry', 'meetingAgenda', 'nominationMeeting')->where('meeting_id', session()->get('meeting_id'))->where('agenda_id', session()->get('meeting_agenda_id'));

        if ($searched) {
            $agendaMinistryList = $agendaMinistryList->where('ministry_id', $search_by);
        }

        $agendaMinistryList = $agendaMinistryList->orderBy('id', 'desc')->paginate(50);


        // $agendaMinistryList = AgendaMinistry::with('meeting', 'ministry', 'meetingAgenda', 'nominationMeeting')->where('meeting_id', session()->get('meeting_id'))->where('agenda_id', session()->get('meeting_agenda_id'))->get();

        $meetings = Meeting::all();
        $meeting_agendas = MeetingAgenda::all();
        $ministries = Ministry::all();
        $nomination_meetings = NominationMeeting::all();
        return view('agenda_ministry.index', compact('agendaMinistryList', 'meetings', 'meeting_agendas', 'ministries', 'nomination_meetings'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'meeting_id' => ['required', 'string', 'max:255'],
            'agenda_id' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'seat_no' => ['required', 'string', 'max:255'],
            'related_ministry' => ['required', 'in:0,1'],
            // 'nomination_meeting_id' => ['required', 'string', 'max:255'],
            // 'completed_status' => ['required', 'in:0,1'],
        ], [
            'ministry_id.required' => 'वैठक नम्बर छान्नुहोस्',
            'seat_no.required' => 'सिट संख्या राख्नुहोस्',
        ]);

        // dd($validatedData);
        $validatedData['created_by'] = auth()->id();
        $addAgendaMinistry = new AgendaMinistry();
        if ($addAgendaMinistry->create($validatedData)) {
            return redirect(make_route('agenda_ministries.index', session()->get('meeting_agenda_id')))->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect(make_route('agenda_ministries.index', session()->get('meeting_agenda_id')))->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('agenda_ministries.index', [session()->get('meeting_agenda_id'), hashtag(session()->get('meeting_agenda_id'))])->withError('अगाडि बढ्न सकिएन।');
        }

        //getting agenda_ministries data releted to id
        $agendaMinistriesInfo = AgendaMinistry::where('id', $id)->firstOrFail();

        $meetings = Meeting::all();
        $meeting_agendas = MeetingAgenda::all();
        $ministries = Ministry::all();
        $nomination_meetings = NominationMeeting::all();

        return view('agenda_ministry.edit', compact('agendaMinistriesInfo', 'meetings', 'meeting_agendas', 'ministries', 'nomination_meetings'));
    }


    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'meeting_id' => ['required', 'string', 'max:255'],
            'agenda_id' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'string', 'max:255'],
            'seat_no' => ['required', 'string', 'max:255'],
            'related_ministry' => ['required', 'in:0,1'],
        ], [
            'ministry_id.required' => 'वैठक नम्बर छान्नुहोस्',
            'seat_no.required' => 'सिट संख्या राख्नुहोस्',
        ]);

        //update data
        $editAgenda = AgendaMinistry::where('id', $id)->firstOrFail();

        //return to agenda_ministry index page if agenda_ministry is updated
        //return to index page if updated
        $oldData = $editAgenda->getOriginal();

        if ($editAgenda->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editAgenda);

            if ($updatedJson)
                $this->actionlog('App\Model\AgendaMinistry', $id, $updatedJson);

            return redirect()->route('agenda_ministries.index', [session()->get('meeting_agenda_id'), hashtag(session()->get('meeting_agenda_id'))])->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting agenda data releted to id
        $agenda = AgendaMinistry::where('id', $id)->firstOrFail();

        $relations = ['applicant_agenda_ministry'];

        if (Delete::check($agenda, $relations))
            if ($agenda->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }

    public function form_two($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('agenda_ministries.index', [session()->get('meeting_agenda_id'), hashtag(session()->get('meeting_agenda_id'))])->withError('अगाडि बढ्न सकिएन।');
        }

        //getting agenda_ministries data releted to id
        $agendaMinistryList = AgendaMinistry::where('id', $id)->with('meeting', 'ministry', 'meetingAgenda', 'nominationMeeting')->firstOrFail();

        $meetings = Meeting::all();
        $meeting_agendas = MeetingAgenda::all();
        $ministries = Ministry::all();
        $nomination_meetings = NominationMeeting::all();

        $reportTemplate = ReportTemplate::where('id', '3')->firstOrFail();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($agendaMinistryList->meeting->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($agendaMinistryList->meeting->meeting_number), $LetterBody);
        $LetterBody = str_replace("{बैठक समय}", EngToUTF8($agendaMinistryList->meeting->meeting_time), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको नाम}", EngToUTF8($agendaMinistryList->meeting->chaired_by), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको पद}", EngToUTF8($agendaMinistryList->meeting->chaired_by_post), $LetterBody);
        $LetterBody = str_replace("{निकायको नाम}", EngToUTF8($agendaMinistryList->ministry->ministry), $LetterBody);
        $LetterBody = str_replace("{निकायको ठेगाना}", EngToUTF8($agendaMinistryList->ministry->address), $LetterBody);
        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);

        return view('agenda_ministry.form-two', compact('agendaMinistryList', 'meetings', 'meeting_agendas', 'ministries', 'nomination_meetings', 'LetterBody'));
    }

    public function form_three($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('agenda_ministries.index', [session()->get('meeting_agenda_id'), hashtag(session()->get('meeting_agenda_id'))])->withError('अगाडि बढ्न सकिएन।');
        }

        //getting agenda_ministries data releted to id
        $agendaMinistryList = AgendaMinistry::where('id', $id)->with('meeting', 'ministry', 'meetingAgenda', 'nominationMeeting')->firstOrFail();

        $meetings = Meeting::all();
        $meeting_agendas = MeetingAgenda::all();
        $ministries = Ministry::all();
        $nomination_meetings = NominationMeeting::all();

        $reportTemplate = ReportTemplate::where('id', '4')->firstOrFail();
        $subBody = ReportTemplate::where('id', '5')->firstOrFail();

        $LetterBody = $reportTemplate->body;

        $LetterBody = str_replace("{बैठक मिति}", EngToUTF8($agendaMinistryList->meeting->meeting_date), $LetterBody);
        $LetterBody = str_replace("{बैठक नम्बर}", EngToUTF8($agendaMinistryList->meeting->meeting_number), $LetterBody);
        $LetterBody = str_replace("{बैठक समय}", EngToUTF8($agendaMinistryList->meeting->meeting_time), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको नाम}", EngToUTF8($agendaMinistryList->meeting->chaired_by), $LetterBody);
        $LetterBody = str_replace("{अध्यक्षता गर्ने पदाधिकारीको पद}", EngToUTF8($agendaMinistryList->meeting->chaired_by_post), $LetterBody);
        $LetterBody = str_replace("{निकायको नाम}", EngToUTF8($agendaMinistryList->ministry->ministry), $LetterBody);
        $LetterBody = str_replace("{निकायको ठेगाना}", EngToUTF8($agendaMinistryList->ministry->address), $LetterBody);
        $LetterBody = str_replace("{फारम भनें अन्तिम मिति}", EngToUTF8($agendaMinistryList->meetingAgenda->form_deadline), $LetterBody);
        $LetterBody = str_replace("{देश}", ($agendaMinistryList->meetingAgenda->country->country), $LetterBody);
        $LetterBody = str_replace("{संस्थाको नाम}", ($agendaMinistryList->meetingAgenda->agency_name), $LetterBody);
        $LetterBody = str_replace("{आजको मिति}", Date::engToNep(date('Y-m-d')), $LetterBody);


        return view('agenda_ministry.form-three', compact('agendaMinistryList', 'meetings', 'meeting_agendas', 'ministries', 'nomination_meetings', 'LetterBody', 'subBody'));
    }

    public function store_report_two(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'report_two' => ['required', 'string'],
        ], [
            'report_two.required' => 'सिट संख्या राख्नुहोस्',
        ]);

        //update data
        $editAgenda = AgendaMinistry::where('id', $id)->firstOrFail();

        $oldData = $editAgenda->getOriginal();

        if ($editAgenda->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editAgenda);

            if ($updatedJson)
                $this->actionlog('App\Model\AgendaMinistry', $id, $updatedJson);

            return back()->withSuccess('सिट बाँडफाट विवरण सम्पादन गरियो।');
        }
        return back()->withError('सिट बाँडफाट विवरण सम्पादन गर्न सकिएन। ')->withInput();
    }

    public function store_report_three(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'report_three' => ['required', 'string'],
        ], [
            'report_three.required' => 'सिट संख्या राख्नुहोस्',
        ]);

        //update data
        $editAgenda = AgendaMinistry::where('id', $id)->firstOrFail();

        $oldData = $editAgenda->getOriginal();

        if ($editAgenda->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editAgenda);

            if ($updatedJson)
                $this->actionlog('App\Model\AgendaMinistry', $id, $updatedJson);

            return back()->withSuccess('छात्रवृत्ति आव्हान पत्र सम्पादन गरियो।');
        }
        return back()->withError('छात्रवृत्ति आव्हान पत्र सम्पादन गर्न सकिएन। ')->withInput();
    }
}
