<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo base abstracto para todas las entidades financieras pertenecientes a un usuario.
 */
abstract class UserOwnedModel extends Model
{
    use BelongsToUser;
}
