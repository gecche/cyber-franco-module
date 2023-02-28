<?php

namespace Modules\CyberFranco\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class PdfRequestVerification extends Model
{
//    use HasFactory;
    use Notifiable;

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return email address only...
        return $this->pdfRequest->email;

        // Return email address and name...
        //return [$this->email_address => $this->name];
    }

    protected $fillable = [
        "pdf_request_id",
        "token",
    ];


    public function pdfRequest(): BelongsTo
    {
        return $this->BelongsTo(PdfRequest::class);
    }

    public static function makeToken()
    {
        return Str::random();
    }

    public function generateNewToken()
    {
        $this->token = $this->makeToken();
        $this->save();
    }

    /*
     * @return static|null
     */
    public static function findFromToken($token) {
        return static::where('token',$token)->first();
    }
}
