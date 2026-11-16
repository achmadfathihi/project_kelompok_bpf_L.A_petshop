<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function open(UserNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 404);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        // Hanya alamat dalam situs ini yang diikuti
        $url = $notification->url;
        $aman = $url && str_starts_with($url, '/') && ! str_starts_with($url, '//');

        return redirect($aman ? $url : route('home'));
    }

    public function readAll()
    {
        UserNotification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}