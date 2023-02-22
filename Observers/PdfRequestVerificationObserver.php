<?php

namespace Modules\CyberFranco\Observers;

use App\Models\PdfRequestVerification;
use App\Notifications\NewPdfRequestEmailToken;

class PdfRequestVerificationObserver
{
    /**
     * Handle the PdfRequestVerification "created" event.
     *
     * @param  \App\Models\PdfRequestVerification  $pdfRequestVerification
     * @return void
     */
    public function created(PdfRequestVerification $pdfRequestVerification)
    {
        $pdfRequestVerification->notify(new NewPdfRequestEmailToken($pdfRequestVerification));
    }

    /**
     * Handle the PdfRequestVerification "updated" event.
     *
     * @param  \App\Models\PdfRequestVerification  $pdfRequestVerification
     * @return void
     */
    public function updated(PdfRequestVerification $pdfRequestVerification)
    {
        $pdfRequestVerification->notify(new NewPdfRequestEmailToken($pdfRequestVerification));
    }

}
