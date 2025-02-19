<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->fiscal_year)) {
            $this->validate($request, [
                'fiscal_year' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'fiscal_year.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->fiscal_year);
        }

        $fiscalYearList = FiscalYear::query();

        if ($searched) {
            $fiscalYearList = $fiscalYearList->where('fiscal_year', 'like', '%' . $search_by . '%');
        }

        $fiscalYearList = $fiscalYearList->orderBy('fiscal_year', 'asc')->paginate(50);

        return view('fiscal_year.index', compact('fiscalYearList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'fiscal_year' => ['required', 'string', 'max:7'],
            'start_date' => ['required', 'string', 'max:10'],
            'end_date' => ['required', 'string', 'max:10'],
        ], [
            'fiscal_year.required' => 'आर्थिक वर्ष राख्नुहोस्',
            'start_date.required' => 'सुरू मिति राख्नुहोस्',
            'end_date.required' => 'अन्त्य मिति राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        $addData = new FiscalYear();
        if ($addData->create($validatedData)) {
            return redirect()->route('fiscal_year.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('fiscal_year.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('fiscal_year.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $fyInfo = FiscalYear::where('id', $id)->firstOrFail();

        return view('fiscal_year.edit', compact('fyInfo'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'fiscal_year' => ['required', 'string', 'max:7'],
            'start_date' => ['required', 'string', 'max:10'],
            'end_date' => ['required', 'string', 'max:10'],
        ], [
            'fiscal_year.required' => 'आर्थिक वर्ष राख्नुहोस्',
            'start_date.required' => 'सुरू मिति राख्नुहोस्',
            'end_date.required' => 'अन्त्य मिति राख्नुहोस्',
        ]);

        //update data
        $editData = FiscalYear::where('id', $id)->firstOrFail();

        $oldData = $editData->getOriginal();

        //return to index page if updated
        if ($editData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editData);

            if ($updatedJson)
                $this->actionlog('App\Model\FiscalYear', $id, $updatedJson);

            return redirect()->route('fiscal_year.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting data releted to id
        $fy = FiscalYear::where('id', $id)->firstOrFail();

        if ($fy->delete()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
        }
        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }
}
