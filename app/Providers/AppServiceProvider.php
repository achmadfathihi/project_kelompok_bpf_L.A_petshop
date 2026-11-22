<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\UserNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use App\Models\Category;
use App\Models\Setting;

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

        Carbon::setLocale('id');

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url   = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);
            $menit = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->subject('Atur Ulang Password ' . config('app.name'))
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Kami menerima permintaan untuk mengatur ulang password akun Anda.')
                ->action('Atur Ulang Password', $url)
                ->line("Tautan ini berlaku {$menit} menit dan hanya bisa dipakai satu kali.")
                ->line('Jika Anda tidak meminta ini, abaikan email ini. Password Anda tidak berubah.')
                ->salutation('Salam, ' . config('app.name'));
        });

        View::composer(['layouts.partials.header', 'layouts.partials.footer'], function ($view) {
            $view->with('tokoTelp', Setting::find('store_phone')?->value);
        });

        View::composer('layouts.partials.header', function ($view) {
            $view->with('menuCategories', Category::orderBy('name')->get(['id', 'name']));
        });
    }
}
