<?php

namespace Modules\CyberFranco\Services\RequestValidators;

use App\Models\PdfRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PdfRequestRequestValidatorFactory
{
    public static function newGenerateValidator(Request $request)
    {

        $user = Auth::user();

        $sources = PdfRequest::optionArray();
        $levels = PdfRequest::levelArray();

        $data = $request->only(['item','level','email','attributes']);
        $data['source'] = $request->route('source');
        $data['user_id'] = $user ? $user->getKey() : null;


        $rules = [
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

        $validator = Validator::make($data, $rules);

        return $validator;
    }


}
