<?php

namespace Modules\CyberFranco\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class PdfRequestHashUuid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $fullModelClassName = config('pdf-request.model_name');

        if (!$fullModelClassName) {
            abort(404);
        }

        $uuid = $request->route('uuid');

        $model = $fullModelClassName::where('uuid',$uuid)->first();

        if (!$model) {
            abort(404);
        }


        $hash = $request->route('hash');

        if ($hash !== hash_uuid($uuid)) {
            abort(404);
        }

        return $next($request);
    }
}
