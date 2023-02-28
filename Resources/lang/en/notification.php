<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during user notification for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'verify-pdf-request'=> [
        'greeting' => 'Hello!',
        'subject'  => 'Cybercheck: pdf report generation about :item',
        'line_1'   => 'Please use the following code to verify the ownership of this email:',
        'line_2'   => 'As soon as you click on the following button, the report generation will start and you will receive an e-mail with the pdf report attached to when ready.',
        'verify-button'   => 'Verify E-mail',
        'reject-button'   => 'Reject PDF Request',
        'notice'   => 'Please note that you have time until :time to verify your e-mail.',
        'contacts' => 'If you have any question please reply to this email, write to <a href="mailto:verification@cyberchecksecurity.com">verification@cyberchecksecurity.com</a> or visit <a href="https://cyberchecksecurity.com/">cyberchecksecurity.com</a>',
        'reject-line'   => 'If you are not interested in receveing the PDF report, please click on the following <a href=":reject_url">link</a> to reject the processing. No further e-meil will be sent',
    ],

];
