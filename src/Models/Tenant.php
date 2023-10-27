<?php

declare(strict_types=1);

namespace Thiagomeloo\Tenant\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Thiagomeloo\Tenant\Events\TenantCreating;

final class Tenant extends Model
{
    use HasUuids;

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            Event::dispatch(new TenantCreating($model));
        });
    }
}
