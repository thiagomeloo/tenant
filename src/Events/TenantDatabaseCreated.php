<?php

declare(strict_types=1);

namespace Thiagomeloo\Tenant\Events;

use Illuminate\Queue\SerializesModels;

final class TenantDatabaseCreated
{
    use SerializesModels;

    public $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }
}
