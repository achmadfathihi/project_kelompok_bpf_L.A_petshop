<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\UserNotification;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('rp', function ($expression) {
            return "<?php echo 'Rp' . number_format((int) ({$expression}), 0, ',', '.'); ?>";
        });

        View::composer(['layouts.partials.bell', 'layouts.admin.bell'], function ($view) {
            $user = auth()->user();

            $view->with([
                'notifUnread' => $user ? UserNotification::where('user_id', $user->id)->whereNull('read_at')->count() : 0,
                'notifList'   => $user ? UserNotification::where('user_id', $user->id)->latest('id')->limit(8)->get() : collect(),
            ]);
        });
    }
}
