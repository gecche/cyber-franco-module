<?php

namespace App\Models;

use App\Services\Cybercheck\CyberCheckApi;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;
use Webpatser\Uuid\Uuid;

/**
 */
class PdfRequest extends \Modules\CyberFranco\Models\PdfRequest
{

}
