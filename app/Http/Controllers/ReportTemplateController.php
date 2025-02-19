<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportTemplate;

class ReportTemplateController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->title)) {
            $this->validate($request, [
                'title' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'title.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->title);
        }

        $ReportTemplateList = ReportTemplate::query();

        if ($searched) {
            $ReportTemplateList = $ReportTemplateList->where('title', 'like', '%' . $search_by . '%');
        }

        $ReportTemplateList = $ReportTemplateList->orderBy('id', 'desc')->paginate(50);

        return view('report_template.index', compact('ReportTemplateList'));
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, [
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        $validatedData['created_by'] = auth()->id();

        $addReportTemplate = new ReportTemplate();
        if ($addReportTemplate->create($validatedData)) {
            return redirect()->route('report_template.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        return redirect()->route('report_template.index')->withError('तथ्यांक थप्न सकिएन।');
    }

    public function edit($id, $hashtag)
    {
        //checking hashtag with id 
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('report_template.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting  data releted to id
        $reportTemplateInfo = ReportTemplate::where('id', $id)->firstOrFail();

        return view('report_template.edit', compact('reportTemplateInfo'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        //update data
        $updateData = ReportTemplate::where('id', $id)->firstOrFail();

        $oldData = $updateData->getOriginal();

        //return to index page if updated
        if ($updateData->update($validatedData)) {

            $updatedJson = get_updated_data($oldData, $updateData);

            if ($updatedJson)
                $this->actionlog('App\Model\ReportTemplate', $id, $updatedJson);

            return redirect()->route('report_template.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
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

        //getting template data releted to id
        $template = ReportTemplate::where('id', $id)->firstOrFail();

        if ($template->delete()) {
            return back()->withSuccess('Template deleted successfully.');
        }
        return back()->withError('Unable to delete template.');
    }
}
