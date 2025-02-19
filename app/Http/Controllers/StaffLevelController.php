<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\StaffLevel;
use Illuminate\Http\Request;

class StaffLevelController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->level)) {
            $this->validate($request, [
                'level' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'level.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->level);
        }

        $staffLevelList = StaffLevel::query();

        if ($searched) {
            $staffLevelList = $staffLevelList->where('staff_level', 'like', '%' . $search_by . '%');
        }

        $staffLevelList = $staffLevelList->orderBy('id', 'desc')->paginate(50);

        return view('staff_level.index', compact('staffLevelList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'staff_level' => ['required', 'string', 'max:255'],
        ], [
            'staff_level.required' => 'श्रेणी/तह राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        $addData = new StaffLevel();
        if ($addData->create($validatedData)) {
            return redirect()->route('staff_level.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('staff_level.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('staff_level.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $staffLevelList = StaffLevel::where('id', $id)->firstOrFail();

        $relations = ['applicant_level'];

        if (Delete::check($staffLevelList, $relations))
            return view('staff_level.edit', compact('staffLevelList'));

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
            'staff_level' => ['required', 'string', 'max:255'],
        ], [
            'staff_level.required' => 'श्रेणी/तह राख्नुहोस्',
        ]);

        //update data
        $editData = StaffLevel::where('id', $id)->firstOrFail();

        $oldData = $editData->getOriginal();

        //return to index page if updated
        if ($editData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editData);

            if ($updatedJson)
                $this->actionlog('App\Model\StaffLevel', $id, $updatedJson);

            return redirect()->route('staff_level.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting data releted to id
        $data = StaffLevel::where('id', $id)->firstOrFail();

        $relations = ['applicant_level'];

        if (Delete::check($data, $relations))
            if ($data->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
