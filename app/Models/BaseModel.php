<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
