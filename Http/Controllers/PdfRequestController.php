<?php

namespace Modules\CyberFranco\Http\Controllers;

use App\Models\PdfRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller;

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

    }

    public function reject(Request $request, $token, $hash)
    {

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
