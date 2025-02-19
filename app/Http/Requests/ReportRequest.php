<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules['report_type'] = ['required', 'numeric', 'in:1,2,3,4'];

        if ($this->report_type == '1')
            $rules['country_id'] = ['required', 'numeric', 'min:1'];

        else if ($this->report_type == '2')
            $rules['program_type_id'] = ['required', 'numeric', 'min:1'];

        else if ($this->report_type == '3')
            $rules['nomination_meeting_id'] = ['required', 'numeric', 'min:1'];

        else if ($this->report_type == '4')
            $rules['letter_to_mof'] = ['required', 'numeric', 'min:1'];

        return $rules;
    }
}
