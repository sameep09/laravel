<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingAttendeeRequest extends FormRequest
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
        // dd($this->all());
        $rules = [
            'meeting_id' => ['required', 'integer', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'seat_no' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'date_type' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'string', 'max:255'],
            'agency_name' => ['required', 'string', 'max:255'],
            'remarks' => ['required', 'string'],
            'final_remarks' => ['nullable', 'string'],
            'source' => ['required', 'string', 'max:255'],
        ];

        if ($this->id) {
            $rules['source_document'] = ['nullable', 'file', 'mimes:pdf'];
        } else {
            $rules['source_document'] = ['required', 'file', 'mimes:pdf'];
        }

        if ($this->date_type === 'AD') {
            $rules['start_date_ad'] = ['required', 'string', 'max:10'];
            $rules['end_date_ad'] = ['required', 'string', 'max:10'];
            $rules['form_deadline_ad'] = ['required', 'string', 'max:10'];
        } else {
            $rules['start_date'] = ['required', 'string', 'max:10'];
            $rules['end_date'] = ['required', 'string', 'max:10'];
            $rules['form_deadline'] = ['required', 'string', 'max:10'];
        }

        if ($this->source === 'अन्य') {
            $rules['source_other'] = ['required', 'string', 'max:255'];
        }



        return $rules;
    }

    public function messages()
    {
        return [
            'subject.required' => 'अध्ययन/तालिमको विषय राख्नुहोस्',
            'seat_no.required' => 'सिट संख्या राख्नुहोस्',
            'type.required' => 'कार्यक्रम प्रकार छान्नुहोस्',
            'date_type.required' => 'मितिको प्रकार छान्नुहोस्',
            'start_date.required' => 'शुरु मिति छान्नुहोस्',
            'start_date_ad.required' => 'शुरु मिति छान्नुहोस्',
            'end_date.required' => 'समाप्ति मिति राख्नुहोस्',
            'end_date_ad.required' => 'समाप्ति मिति राख्नुहोस्',
            'country_id.required' => 'देश छान्नुहोस्',
            'agency_name.required' => 'संस्थाको नाम राख्नुहोस्',
            'form_deadline.required' => 'फाराम बुझाउने अन्तिम मिति राख्नुहोस्',
            'form_deadline_ad.required' => 'फाराम बुझाउने अन्तिम मिति राख्नुहोस्',
            'remarks.required' => 'आवश्यक योग्यता/अन्य प्रासंगिक कुरा राख्नुहोस्',
            'source.required' => 'श्रोत छान्नुहोस्',
            'source_other.required' => 'अन्य स्रोत खुलाउनुहोस्',
            'source_document.required' => 'श्रोतको चिठी राख्नुहोस्',
            'source_document.mimes' => 'श्रोतको चिठीको प्रकार मिलेन',
        ];
    }
}
