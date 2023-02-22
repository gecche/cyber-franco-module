<?php

namespace Modules\CyberFranco\Notifications;

use App\Models\User;
use App\Services\Tracking\MixPanel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class NewPdfRequestEmailToken extends Notification
{
    use Queueable;

    private $token;
    private $userId;

    private $ip;
    private $ua;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($pdfRequestVerification)
    {
        $this->token = $pdfRequestVerification->token;
        $this->userId = $pdfRequestVerification->pdfRequestItem->user_id;

        $this->ip = request()->ip();
        $this->ua = request()->userAgent();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user = Auth::user();
        if ($this->userId) {
            $user = User::find($this->userId);
        }
        $footer = $this->getFooter($user);
        $logo = $this->getLogo($user);
        $trackingPixel = $this->getTrackingPixel($user);

        return (new MailMessage)
            ->subject(Lang::get('notification.verify-message.subject'))
            ->line(Lang::get('notification.verify-message.line_1'))
            ->line(new HtmlString('<div class="info-block"><p>'. $this->token .'</p></div>'))
            ->line(Lang::get('notification.verify-message.line_2'))
            ->line(new HtmlString('<br>'))
            ->line('This request was sent from '. $this->getIp() .' using '. $this->getBrowsers($this->ua) .' on '. $this->getOs($this->ua) ." from $user->email. If you didnt ask for it, you can safely ignore this message: no data about your email will be shared with anyone.")
            ->line(new HtmlString(Lang::get('notification.verify-message.contacts')))
            ->markdown("notifications::email", ['logo' => $logo, 'trackingPixel' => $trackingPixel, 'footer' => $footer]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return request()->ip(); // it will return server ip when no client ip found
    }

    private function getLogo($user)
    {
        $reseller = $user->customer->reseller;
        if ($reseller->logo) {
            $disk = config('filesystems.default');
            $url = Storage::disk($disk)->url($reseller->logo);
            return $url;
        }
        return null;
    }

    private function getTrackingPixel($notifiable)
    {
        $pixelEventName = config('tracking.mixpanel.events.open-new-hits-email');
        $extraProps = [
            "user-id" => $notifiable->id,
        ];
        $trackingPixel = MixPanel::createPixel($pixelEventName, $extraProps);
        return $trackingPixel;
    }

    private function getFooter($user)
    {
        $reseller = $user->reseller;
        return ($reseller && $reseller->footer) ? $reseller->footer : null;
    }

    public static function getOs($user_agent){
        $os_platform = "Unknown OS Platform";
        $os_array = array(
            '/windows nt 13/i'  => 'Windows 11',
            '/windows nt 10/i'  => 'Windows 10',
            '/windows nt 6.3/i'  => 'Windows 8.1',
            '/windows nt 6.2/i'  => 'Windows 8',
            '/windows nt 6.1/i'  => 'Windows 7',
            '/windows nt 6.0/i'  => 'Windows Vista',
            '/windows nt 5.2/i'  => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'  => 'Windows XP',
            '/windows xp/i'  => 'Windows XP',
            '/windows nt 5.0/i'  => 'Windows 2000',
            '/windows me/i'  => 'Windows ME',
            '/win98/i'  => 'Windows 98',
            '/win95/i'  => 'Windows 95',
            '/win16/i'  => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'  => 'Mac OS 9',
            '/linux/i'  => 'Linux',
            '/ubuntu/i'  => 'Ubuntu',
            '/iphone/i'  => 'iPhone',
            '/ipod/i'  => 'iPod',
            '/ipad/i'  => 'iPad',
            '/android/i'  => 'Android',
            '/blackberry/i'  => 'BlackBerry',
            '/webos/i'  => 'Mobile',
        );

        foreach ($os_array as $regex => $value){
            if(preg_match($regex, $user_agent)){
                $os_platform = $value;
            }
        }
        return $os_platform;
    }

    public static function getBrowsers($user_agent){
        $browser = "Unknown Browser";

        $browser_array = array(
            '/msie/i'  => 'Internet Explorer',
            '/Trident/i'  => 'Internet Explorer',
            '/firefox/i'  => 'Firefox',
            '/safari/i'  => 'Safari',
            '/chrome/i'  => 'Chrome',
            '/edge/i'  => 'Edge',
            '/opera/i'  => 'Opera',
            '/netscape/'  => 'Netscape',
            '/maxthon/i'  => 'Maxthon',
            '/knoqueror/i'  => 'Konqueror',
            '/ubrowser/i'  => 'UC Browser',
            '/mobile/i'  => 'Safari Browser',
        );

        foreach($browser_array as $regex => $value){
            if(preg_match($regex, $user_agent)){
                $browser = $value;
            }
        }
        return $browser;
    }
}
