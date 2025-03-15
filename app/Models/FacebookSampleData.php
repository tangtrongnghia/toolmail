<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookSampleData extends Model
{
    const PENDING = 0;
    const PROCESSING = 1;
    const SUCCESS = 2;

    public $timestamps = true;
    protected $table = 'facebook_sample_data';
    protected $fillable = [
        'user_id',
        'last_name',
        'first_name',
        'phone',
        'email',
        'password',
        'facebook_link',
        'facebook_uid',
        'two_fa_secret',
        'status',
    ];

    public function getStatusAttribute()
    {
        if ($this->attributes['status'] == self::PROCESSING && $this->updated_at->diffInMinutes(now()) > 30) {
            return self::PENDING;
        }

        return $this->attributes['status'];
    }
}
