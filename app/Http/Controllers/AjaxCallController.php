<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Meeting;
use App\Models\ProgramType;
use Illuminate\Http\Request;
use App\Models\NominationMeeting;
use PHPUnit\Framework\Constraint\Count;

class AjaxCallController extends Controller
{
    public function get_report_option_by_type(Request $request)
    {
        //validating request
        $validatedData = $this->validate($request, [
            'report_type' => ['required', 'numeric', 'in:1,2,3,4']
        ]);

        $reportType = sanitize($request->report_type);

        switch ($reportType) {
            case '1': //देश अनुसार प्रतिवेदन
                $arrayData = Country::orderBy('country')->get()->map->forAjax();
                break;

            case '2': //कार्यक्रमको प्रकार अनुसार प्रतिवेदन
                $arrayData = ProgramType::orderBy('type')->get()->map->forAjax();
                break;

            case '3': //बैठक अनुसार मनोनयन सिफारिस प्रतिवेदन
                $arrayData = NominationMeeting::orderByDesc('meeting_date')->get()->map->forAjax();
                break;

            case '4': //अर्थ मन्त्रालयलाई चिठी
                $arrayData = NominationMeeting::orderByDesc('meeting_date')->get()->map->forAjax();
                break;

            case '5': //मन्त्रालयलाई चिठी
                $arrayData = Meeting::orderByDesc('meeting_date')->get()->map->forAjax();
                break;

            default:
                $arrayData = [];
                break;
        }

        return $arrayData;
    }
}
