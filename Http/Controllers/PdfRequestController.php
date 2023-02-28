<?php

namespace Modules\CyberFranco\Http\Controllers;

use App\Models\PdfRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;
use App\Models\PdfRequestVerification;

class PdfRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    //  route per la generazione dei pdf da fonti note (lexy)
    //In post:
    //token (di lexy) da controllare anche con la source e la origin della request
    //item
    //email
    //level
    //attributes (eventuali info agiguntive sul pdf da generare)
    public function generate(Request $request, $source)
    {
        $pdfRequestData = $request->only(['item','email','level','attributes']);
        $pdfRequestData['source'] = $source;
        $user = Auth::user();
        $pdfRequestData['user_id'] = $user ? $user->getKey() : null;

        $pdfRequest = PdfRequest::create($pdfRequestData);

        return Response::json($pdfRequest->fresh());

    }

    //Il token è un verification token temporaneo (unica route dove passa)
    //hash è l'hash generato dall'uuid della richiesta
    public function verify(Request $request, $token, $hash)
    {
        $pdfRequest = $this->checkVerification($token,$hash,'Accept');
        $pdfRequest->makeTransitionAndSave('in_progress',["Verification e-mail done"]);
        return "Verification e-mail done";
    }

    public function reject(Request $request, $token, $hash)
    {
        $pdfRequest = $this->checkVerification($token,$hash,'Reject');
        $pdfRequest->makeTransitionAndSave('rejected',["Verification e-mail rejected"]);
        return "PDF Request rejected";
    }


    /*
     * @return App\Models\PdfRequest
     */
    protected function checkVerification($token, $hash, $type) {
        $verification = PdfRequestVerification::findFromToken($token);
        $prefixMsg = $type . ' PDF Request::: ';
        if (!$verification) {
            Log::info($prefixMsg."Verification not found, token: " . $token);
            abort(404);
        }
        //CHECK PDF REQUEST STATUS AND VERIFICATION EXPIRATION
        $pdfRequest = $verification->pdfRequest;
        if (!$pdfRequest) {
            Log::info($prefixMsg."From Verification not found, verification id: " . $verification->getKey());
            abort(404);
        }
        if (!$pdfRequest->toBeVerified()) {
            Log::info($prefixMsg."Verification not to be verified: " . $pdfRequest->getKey());
            abort(404);
        }
        if ($pdfRequest->isVerificationExpired()) {
            Log::info($prefixMsg."Verification is expired: " . $pdfRequest->getKey());
            abort(404);
        }
        //CHECK PDF REQUEST HASH
        if ($hash !== $pdfRequest->getHash()) {
            Log::info($prefixMsg."Hash UUID does not match (HASH --> PDFHASH): " . $hash . '-->' . $pdfRequest->getHash());
            abort(404);
        }
        //TUTTO OK: CANCELLO LA VERIFICATION E RITORNO LA PDF REQUEST
        //$verification->delete();
        return $pdfRequest;

    }

    //hash è l'uuid della richiesta
    //hash è l'hash generato dall'uuid della richiesta
    public function resendVerification(Request $request, $uuid, $hash)
    {

    }


    public function getStatus(Request $request, $uuid, $hash)
    {

    }

}
