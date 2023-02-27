<?php

namespace Modules\CyberFranco\Models;

use App\Services\Cybercheck\CyberCheckApi;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
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
use Webpatser\Uuid\Uuid;
use App\Models\User;

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
        return $this->HasOne(PdfRequestVerification::class);
    }

    public function generateVerification()
    {
        if ($this->type == 'email') {
            $verification = $this->verification()->create([
                'token' => MonitorVerification::makeToken()
            ]);
        } else {
            $this->verified = true;
            $this->save();
            $verification = true;
        }

        return $verification;
    }

    /**
     * Scope a query to only include active items of a customers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Customer $customer
     * @return void
     */
    public function scopeCustomerList($query, Customer $customer)
    {
        $query->where('customer_id', $customer->getKey())
            ->active()
            ->verified();
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

}
