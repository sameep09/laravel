<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\StaffService;
use Illuminate\Http\Request;

class StaffServiceController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->service)) {
            $this->validate($request, [
                'service' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'service.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->service);
        }

        $staffServiceList = StaffService::query();

        if ($searched) {
            $staffServiceList = $staffServiceList->where('staff_service', 'like', '%' . $search_by . '%');
        }

        $staffServiceList = $staffServiceList->orderBy('id', 'desc')->paginate(50);

        return view('staff_service.index', compact('staffServiceList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'staff_service' => ['required', 'string', 'max:255'],
        ], [
            'staff_service.required' => 'सेवा राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        $addData = new StaffService();
        if ($addData->create($validatedData)) {
            return redirect()->route('staff_service.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('staff_service.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('staff_service.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $staffServiceList = StaffService::where('id', $id)->firstOrFail();

        $relations = ['applicant_service'];

        if (Delete::check($staffServiceList, $relations))
            return view('staff_service.edit', compact('staffServiceList'));

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
            'staff_service' => ['required', 'string', 'max:255'],
        ], [
            'staff_service.required' => 'सेवा राख्नुहोस्',
        ]);

        //update data
        $editData = StaffService::where('id', $id)->firstOrFail();

        $oldData = $editData->getOriginal();

        //return to index page if updated
        if ($editData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editData);

            if ($updatedJson)
                $this->actionlog('App\Model\StaffService', $id, $updatedJson);

            return redirect()->route('staff_service.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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
        $data = StaffService::where('id', $id)->firstOrFail();

        $relations = ['applicant_service'];

        if (Delete::check($data, $relations))
            if ($data->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
