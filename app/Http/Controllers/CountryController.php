<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\Country;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class CountryController extends Controller
{

    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->country_name)) {
            $this->validate($request, [
                'country_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'country_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->country_name);
        }

        $countryList = Country::query();

        if ($searched) {
            $countryList = $countryList->where('country', 'like', '%' . $search_by . '%');
        }

        $countryList = $countryList->paginate(50);

        return view('country.index', compact('countryList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'country' => ['required', 'string', 'max:255'],
        ], [
            'country.required' => 'देशको नाम राख्नुहोस्',
        ]);

        // dd($validatedData);
        $validatedData['created_by'] = auth()->id();
        $addCountry = new Country();
        if ($addCountry->create($validatedData)) {
            return redirect()->route('country.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('country.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('country.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting country data releted to id
        $countryInfo = Country::where('id', $id)->firstOrFail();

        $relations = ['meeting_agenda', 'study_leave'];

        if (Delete::check($countryInfo, $relations))
            return view('country.edit', compact('countryInfo'));

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
            'country' => ['required', 'string', 'max:255'],
        ], [
            'country.required' => 'देशको नाम राख्नुहोस्',
        ]);

        //update data
        $editCountry = Country::where('id', $id)->firstOrFail();

        $oldData = $editCountry->getOriginal();

        //return to index page if updated
        if ($editCountry->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editCountry);

            if ($updatedJson)
                $this->actionlog('App\Model\Country', $id, $updatedJson);

            return redirect()->route('country.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting Country data releted to id
        $country = Country::where('id', $id)->firstOrFail();

        $relations = ['meeting_agenda', 'study_leave'];

        if (Delete::check($country, $relations))
            if ($country->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
