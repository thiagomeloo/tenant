<?php

namespace Thiagomeloo\Tenant\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Thiagomeloo\Tenant\Events\TenantCreating;

class Tenant extends Model
{
    use HasUuids;

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            Event::dispatch(new TenantCreating($model));
        });
    }
}
