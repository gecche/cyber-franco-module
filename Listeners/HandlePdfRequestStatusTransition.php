<?php

namespace Modules\CyberFranco\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Gecche\FSM\Events\StatusTransitionDone;
use Modules\CyberFranco\Notifications\NewPdfRequestEmailToken;

class HandlePdfRequestStatusTransition
{

    protected $model;
    protected $prevStatusCode;
    protected $statusCode;
    protected $statusData;
    protected $saved;
    protected $params;


    public function handle(StatusTransitionDone $event)
    {

        $this->model = $event->model;
        $this->prevStatusCode = $event->prevStatusCode;
        $this->statusCode = $event->statusCode;
        $this->statusData = $event->statusData;
        $this->saved = $event->saved;
        $this->params = $event->params;

        $methodName = 'handleTransitionFrom'.Str::studly($event->prevStatusCode).'To'.Str::studly($event->statusCode);
        if (method_exists($this,$methodName)) {
            return $this->$methodName();
        }

        $this->handleTransition();
    }

    protected function handleTransition() {
        $fsm = $this->model->getFsm();
        $rootState = $fsm->getRootState();
        switch ($this->statusCode) {

            case $rootState:
                if ($this->model->needsVerification()) {
                    $this->model->makeTransitionAndSave('in_verification');
                } else {
                    $this->model->makeTransitionAndSave('in_progress',["No need for verification: " . $this->model->source ." PDF request"]);
                }
                break;
            case 'in_verification':
                $verification = $this->model->generateVerification();
                $verification->notify(new NewPdfRequestEmailToken($verification));
            case 'in_progress':
                //START PDF REQUEST JOB
                Log::info("REQUEST PDF JOB STARTED");
                break;
            default:
                break;

        }
    }

}
