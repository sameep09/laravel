<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\Ministry;
use Illuminate\Http\Request;

class MinistryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->ministry_name)) {
            $this->validate($request, [
                'ministry_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'ministry_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->ministry_name);
        }

        $ministryList = Ministry::query();

        if ($searched) {
            $ministryList = $ministryList->where('ministry', 'like', '%' . $search_by . '%');
        }

        $ministryList = $ministryList->orderBy('id', 'desc')->paginate(50);

        return view('ministry.index', compact('ministryList'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'ministry' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'default_ministry' => ['required', 'string', 'in:0,1']
        ], [
            'ministry.required' => 'मन्त्रालयको नाम राख्नुहोस्',
            'address.required' => 'मन्त्रालयको ठेगाना राख्नुहोस्',
            'default_ministry.required' => 'डिफल्ट मन्त्रालय हो वा होइन छान्नुहोस',
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addMinistry = new Ministry();
        if ($addMinistry->create($validatedData)) {
            return redirect()->route('ministry.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('ministry.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('ministry.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting ministry data releted to id
        $ministryInfo = Ministry::where('id', $id)->firstOrFail();

        $relations = ['meeting_attendees', 'agenda_ministry'];

        if (Delete::check($ministryInfo, $relations))
            return view('ministry.edit', compact('ministryInfo'));

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले सम्पादन गर्नु सकिएन।');
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'ministry' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'default_ministry' => ['required', 'in:0,1'],
        ], [
            'ministry.required' => 'मन्त्रालयको नाम राख्नुहोस्',
            'address.required' => 'मन्त्रालयको ठेगाना राख्नुहोस्',
            'default_ministry.required' => 'डिफल्ट मन्त्रालय हो वा होइन छान्नुहोस',
        ]);

        //update data
        $editMinistry = Ministry::where('id', $id)->firstOrFail();

        $oldData = $editMinistry->getOriginal();

        //return to index page if updated
        if ($editMinistry->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editMinistry);

            if ($updatedJson)
                $this->actionlog('App\Model\Ministry', $id, $updatedJson);

            return redirect()->route('ministry.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting ministry data releted to id
        $ministry = Ministry::where('id', $id)->firstOrFail();

        $relations = ['meeting_attendees', 'agenda_ministry'];

        if (Delete::check($ministry, $relations))
            if ($ministry->delete()) {
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
            }

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
