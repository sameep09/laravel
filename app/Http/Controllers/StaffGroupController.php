<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\StaffGroup;
use Illuminate\Http\Request;

class StaffGroupController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->group)) {
            $this->validate($request, [
                'group' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'group.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->group);
        }

        $staffGroupList = StaffGroup::query();

        if ($searched) {
            $staffGroupList = $staffGroupList->where('staff_group', 'like', '%' . $search_by . '%');
        }

        $staffGroupList = $staffGroupList->orderBy('id', 'desc')->paginate(50);

        return view('staff_group.index', compact('staffGroupList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'staff_group' => ['required', 'string', 'max:255'],
        ], [
            'staff_group.required' => 'समुह राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        $addData = new StaffGroup();
        if ($addData->create($validatedData)) {
            return redirect()->route('staff_group.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('staff_group.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('staff_group.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $staffGroupList = StaffGroup::where('id', $id)->firstOrFail();

        $relations = ['applicant_group'];

        if (Delete::check($staffGroupList, $relations))
            return view('staff_group.edit', compact('staffGroupList'));

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
            'staff_group' => ['required', 'string', 'max:255'],
        ], [
            'staff_group.required' => 'समुह राख्नुहोस्',
        ]);

        //update data
        $editData = StaffGroup::where('id', $id)->firstOrFail();

        $oldData = $editData->getOriginal();

        //return to index page if updated
        if ($editData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editData);

            if ($updatedJson)
                $this->actionlog('App\Model\StaffGroup', $id, $updatedJson);

            return redirect()->route('staff_group.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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
        $data = StaffGroup::where('id', $id)->firstOrFail();

        $relations = ['applicant_group'];

        if (Delete::check($data, $relations))
            if ($data->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
