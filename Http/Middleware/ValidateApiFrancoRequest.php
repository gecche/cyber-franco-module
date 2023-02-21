<?php

namespace Modules\CyberFranco\Http\Middleware;

use App\Services\RequestValidators\PdfRequestRequestValidatorFactory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidateApiFrancoRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $validator = self::createValidator($request);
        if ($validator && $validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }

    public static function createValidator(Request $request)
    {
        $routeName = $request->route()->getName();
        $validator = null;
        switch ($routeName) {

            case 'pdf-request.generate':
                $validator = PdfRequestRequestValidatorFactory::newGenerateValidator($request);
                break;
            default:
                break;
        }

        return $validator;
    }
}
