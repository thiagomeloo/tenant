<?php

namespace Thiagomeloo\Tenant\Events;

use Illuminate\Queue\SerializesModels;

class TenantDatabaseCreated
{
  public $tenant;

  use SerializesModels;

  public function __construct($tenant)
  {
    $this->tenant = $tenant;
  }
}
