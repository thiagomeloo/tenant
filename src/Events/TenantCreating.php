<?php

namespace Thiagomeloo\Tenant\Events;

use Illuminate\Queue\SerializesModels;

class TenantCreating
{
  public $tenant;

  use SerializesModels;

  public function __construct($tenant)
  {
    $this->tenant = $tenant;
  }
}
