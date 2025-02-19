<?php

namespace App\Http\Controllers;

use App\Models\Delete;
use App\Models\StaffPost;
use Illuminate\Http\Request;

class StaffPostController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->post)) {
            $this->validate($request, [
                'post' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'post.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->post);
        }

        $staffPostList = StaffPost::query();

        if ($searched) {
            $staffPostList = $staffPostList->where('staff_post', 'like', '%' . $search_by . '%');
        }

        $staffPostList = $staffPostList->orderBy('post_order', 'asc')->paginate(50);

        return view('staff_post.index', compact('staffPostList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'post_order' => ['required', 'integer', 'min:0'],
            'staff_post' => ['required', 'string', 'max:255'],
        ], [
            'post_order.required' => 'पद क्र.सं. राख्नुहोस्',
            'staff_post.required' => 'पद राख्नुहोस्',
        ]);

        $validatedData['created_by'] = auth()->id();

        $addData = new StaffPost();
        if ($addData->create($validatedData)) {
            return redirect()->route('staff_post.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('staff_post.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('staff_post.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting data releted to id
        $staffPostList = StaffPost::where('id', $id)->firstOrFail();

        $relations = ['meeting_post', 'nom_meeting_post', 'applicant_post'];

        if (Delete::check($staffPostList, $relations))
            return view('staff_post.edit', compact('staffPostList'));

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
            'post_order' => ['required', 'integer', 'min:0'],
            'staff_post' => ['required', 'string', 'max:255'],
        ], [
            'post_order.required' => 'पद क्र.सं. राख्नुहोस्',
            'staff_post.required' => 'पद राख्नुहोस्',
        ]);

        //update data
        $editData = StaffPost::where('id', $id)->firstOrFail();

        $oldData = $editData->getOriginal();

        //return to index page if updated
        if ($editData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $editData);

            if ($updatedJson)
                $this->actionlog('App\Model\StaffPost', $id, $updatedJson);

            return redirect()->route('staff_post.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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
        $data = StaffPost::where('id', $id)->firstOrFail();

        $relations = ['meeting_post', 'nom_meeting_post', 'applicant_post'];

        if (Delete::check($data, $relations))
            if ($data->delete())
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');

        return back()->withError('तथ्यांक प्रयोगमा रहेकोले मेटाउन सकिएन।');
    }
}
