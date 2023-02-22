<?php

namespace Modules\CyberFranco\Http\Requests;

use App\Models\PdfRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePdfRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
//        $item = $this->item;

        $sources = PdfRequest::optionArray();
        $levels = PdfRequest::levelArray();



//        $userId = request()->get('user_id');



        return [
            'item' => [
                'required',
                'min:5',
            ],
            'source' => [
                'required',
                Rule::in(array_keys($sources)),
            ],
            'level' => [
                'required',
                Rule::in(array_keys($levels)),
            ],
            'email' => ['required_unless:source,internal',
            ],
            'user_id' => ['required_if:source,internal',
            ],
            'attributes' => [
//                new PdfRequestAssociations($customerId,$userId,($profilesIds ?: []))
            ],

        ];
    }
}
