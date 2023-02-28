<?php

namespace Modules\CyberFranco\Models;

use App\Services\Cybercheck\CyberCheckApi;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Exception;
use Gecche\FSM\Events\StatusTransitionDone;
use Gecche\FSM\FSMTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;
use App\Models\User;
use App\Models\PdfRequestVerification;

/**
 */
class PdfRequest extends Model
{
    use CrudTrait;
    use FSMTrait;

//    use HasFactory;

    public const SOURCES = [
        'internal' => 'Internal',
        'lexy' => 'Lexy',
    ];

    protected $fillable = [
        'source',
        'email',
        'user_id',
        'item',
        'level',
        'attributes',
        'status',
        'status_history',
        'filename',
        'verified',
        'active',
        'backpack',
    ];

    public $appends = [
        'emailuser'
    ];

    public function type(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value,
            set: fn($value) => $value,
        );
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeInactive($query)
    {
        $query->where('active', 0);
    }

    /**
     * Scope a query to only include verified items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeVerified($query)
    {
        $query->where('verified', 1);
    }

    /**
     * Scope a query to only include not verified items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeUnverified($query)
    {
        $query->where('verified', 0);
    }

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function verification(): HasOne
    {
        return $this->HasOne(PdfRequestVerification::class,'pdf_request_id');
    }

    public function generateVerification()
    {
        $verification = null;
        if ($this->needsVerification()) {
            $verification = $this->verification()->create([
                'token' => PdfRequestVerification::makeToken()
            ]);
        }
        return $verification;
    }


    public function getItemUserAttribute($value)
    {
        return $this->item .
            ($this->user_id
                ? ' - User: ' . $this->user->name
                : " - Any user");
    }

    public static function optionArray($type = null)
    {
        return Config::get('pdf_request.sources',[]);
    }

    public static function levelArray($type = null)
    {
        return [1 => "1 - Basic",2 => "2 - Full"];
    }

    public function getEmailuserAttribute() {
        if ($this->email) {
            return $this->email;
        }
        return $this->user ? $this->user->name : '--';
    }

    public function needsVerification() {
        return ($this->status === $this->fsm->getRootState()) && ($this->source !== 'internal');
    }

    public function save(array $options = [])
    {
        $isBackpack = isset($this->attributes['backpack']) && $this->attributes['backpack'];
        unset($this->attributes['backpack']);
        $isInsert = !$this->getKey();
        if ($isInsert) {

            if ($this->source == 'internal') {
                if ($this->user) {
                    $this->email = $this->user->email;
                }
            } else {
                $this->user_id = null;
            }

            //FROM BACKPACK
            if ($isBackpack) {
                $this->startFSM(false,[],['fireEvent' => false]);
            }
        }

        if (!$this->uuid) {
            $trovato = true;
            while ($trovato) {
                $uuid = (string) Uuid::generate(4);
                $dealCount = static::where('uuid', $uuid)->count();
                if ($dealCount < 1) {
                    $this->uuid = $uuid;
                    $trovato = false;
                }
            }
        }

        $saved = parent::save($options);
        if ($isBackpack && $isInsert) {
            event(new StatusTransitionDone($this, null, $this->fsm->getRootState()));
        }
        return $saved;  // TODO: Change the autogenerated stub
    }

    public function getHash() {
        return hash_uuid($this->uuid);
    }

    /*
     * @return: Carbon/Carbon|false
     */
    public function getVerificationExpirationTime() {
        if (!$this->needsVerification()) {
            return false;
        }
        try {
            return Carbon::parse($this->created_at)->addDays(config('pdf_request.verification_expiration_days',7));
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getExpirationTime() {
        try {
            return Carbon::parse($this->created_at)->addDays(config('pdf_request.expiration_days',365));
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function isVerificationExpired() {
        if (!$this->toBeVerified()) {
            return false;
        }

        $deadline = $this->getVerificationExpirationTime();
        if (!$deadline) {
            return false;
        }

        if ($deadline->greaterThan(now())) {
            return false;
        }
        return true;
    }

    public function toBeVerified() {
        return $this->status == 'in_verification';
    }


}
