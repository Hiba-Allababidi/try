<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class User_verification extends Model
{
    use HasFactory;

    protected $fillable=['user_id','code'];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

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
