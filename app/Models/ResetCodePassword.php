<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $email)
 * @method static firstWhere(string $string, $code)
 */
class ResetCodePassword extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'created_at',
        'updated_at'
    ];

    /**
     * check if the code is expired then deletes it
     *
     * @return bool
     */

    public function isExpire()
    {
        if ($this->created_at > now()->addHour()) {
            $this->delete();
            return true;
        }
        return false;
    }
}
