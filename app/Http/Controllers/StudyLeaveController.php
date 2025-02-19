<?php

namespace App\Http\Controllers;

use App\Models\StudyLeave;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Ministry;

class StudyLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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

        $studyLeaveList = StudyLeave::query()->with('country', 'ministry');

        if ($searched) {
            $studyLeaveList = $studyLeaveList->where('name', 'like', '%' . $search_by . '%');
        }

        $studyLeaveList = $studyLeaveList->orderBy('id', 'desc')->paginate(50);

        // $studyLeaveList = StudyLeave::query()->with('country', 'ministry')->orderBy('id', 'desc')->get();

        $countries = Country::all();
        $ministries = Ministry::all();

        return view('study_leave.index', compact('studyLeaveList', 'countries', 'ministries'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'sanket_no' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'approval_date' => ['required', 'string', 'max:255'],
            'return_date' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'min:1'],
            'program_type' => ['required', 'integer', 'min:0'],
            'program' => ['required', 'string', 'max:255'],
            'current_office' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'string', 'max:255'],
            'period' => ['required', 'string', 'max:255'],
        ], [
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'name.required' => 'नाम राख्नुहोस्',
            'approval_date.required' => 'निर्णय मिति राख्नुहोस्',
            'return_date.required' => 'समाप्ति मिति राख्नुहोस्',
            'country_id.required' => 'देश छान्नुहोस्',
            'program_type.required' => 'कार्यक्रमको प्रकार छान्नुहोस्',
            'program.required' => 'अध्ययन/तालिमको विषय राख्नुहोस्',
            'current_office.required' => 'कार्यरत निकाय राख्नुहोस्',
            'ministry_id.required' => 'सम्बन्धित मन्त्रालय छान्नुहोस्',
            'start_date.required' => 'सुरु मिति राख्नुहोस्',
            'period.required' => 'अवधि राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addStudyLeave = new StudyLeave();
        if ($addStudyLeave->create($validatedData)) {
            return redirect()->route('study_leave.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('study_leave.index')->withError('तथ्यांक थप्न सकिएन।');
    }


    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('study_leave.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting studyLeave data releted to id
        $studyLeaveInfo = StudyLeave::where('id', $id)->firstOrFail();

        $countries = Country::all();
        $ministries = Ministry::all();

        return view('study_leave.edit', compact('studyLeaveInfo', 'countries', 'ministries'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'sanket_no' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'approval_date' => ['required', 'string', 'max:255'],
            'return_date' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'min:1'],
            'program_type' => ['required', 'integer', 'min:0'],
            'program' => ['required', 'string', 'max:255'],
            'current_office' => ['required', 'string', 'max:255'],
            'ministry_id' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'string', 'max:255'],
            'period' => ['required', 'string', 'max:255'],
        ], [
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'name.required' => 'नाम राख्नुहोस्',
            'approval_date.required' => 'निर्णय मिति राख्नुहोस्',
            'return_date.required' => 'समाप्ति मिति राख्नुहोस्',
            'country_id.required' => 'देश छान्नुहोस्',
            'program_type.required' => 'कार्यक्रमको प्रकार छान्नुहोस्',
            'program.required' => 'अध्ययन/तालिमको विषय राख्नुहोस्',
            'current_office.required' => 'कार्यरत निकाय राख्नुहोस्',
            'ministry_id.required' => 'सम्बन्धित मन्त्रालय छान्नुहोस्',
            'start_date.required' => 'सुरु मिति राख्नुहोस्',
            'period.required' => 'अवधि राख्नुहोस्',
        ]);


        //update data
        $editStudyLeave = StudyLeave::where('id', $id)->firstOrFail();

        $oldData = $editStudyLeave->getOriginal();

        //return to index page if updated
        if ($editStudyLeave->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editStudyLeave);

            if ($updatedJson)
                $this->actionlog('App\Model\StudyLeave', $id, $updatedJson);

            return redirect()->route('study_leave.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting studyLeave data releted to id
        $studyLeave = StudyLeave::where('id', $id)->firstOrFail();

        if ($studyLeave->delete()) {
            return back()->withSuccess('StudyLeave deleted successfully.');
        }
        return back()->withError('Unable to delete StudyLeave.');
    }
}
