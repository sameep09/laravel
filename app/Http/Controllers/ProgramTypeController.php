<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\ProgramType;
use Illuminate\Http\Request;

class ProgramTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searched = false;


        if (isset($request->s_tk) && isset($request->type_name)) {
            $this->validate($request, [
                'type_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'type_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->type_name);
        }

        $pTypeList = ProgramType::query();

        if ($searched) {
            $pTypeList = $pTypeList->where('type', 'like', '%' . $search_by . '%');
        }

        $pTypeList = $pTypeList->orderBy('id', 'desc')->paginate(50);

        return view('program_type.index', compact('pTypeList'));
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
            'type' => ['required', 'string', 'max:255'],
        ], [
            'type.required' => 'कार्यक्रम प्रकार राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        // dd($validatedData);
        $addpType = new ProgramType();
        if ($addpType->create($validatedData)) {
            return redirect()->route('pType.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('pType.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('program_type.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting Program Type data releted to id
        $pTypeInfo = ProgramType::where('id', $id)->firstOrFail();

        $relations = ['meeting_agenda'];

        if (Delete::check($pTypeInfo, $relations))
            return view('program_type.edit', compact('pTypeInfo'));

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
            'type' => ['required', 'string', 'max:255'],
        ], [
            'type.required' => 'कार्यक्रम प्रकार राख्नुहोस्',
        ]);

        //update data
        $editpType = ProgramType::where('id', $id)->firstOrFail();

        $oldData = $editpType->getOriginal();

        //return to index page if updated
        if ($editpType->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editpType);

            if ($updatedJson)
                $this->actionlog('App\Model\ProgramType', $id, $updatedJson);

            return redirect()->route('pType.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting Program Type data releted to id
        $pType = ProgramType::where('id', $id)->firstOrFail();

        $relations = ['meeting_agenda'];

        if (Delete::check($pType, $relations))
            if ($pType->delete()) {
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
            }
        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
