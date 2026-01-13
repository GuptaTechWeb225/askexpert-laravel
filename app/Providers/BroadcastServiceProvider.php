<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  public function boot()
    {
        // 🔥 Yeh line change karo – admin guard bhi add kar do
        Broadcast::routes(['middleware' => ['web', 'auth:customer,expert,admin']]);

        require base_path('routes/channels.php');
    }
}
