<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class Notifier
{
    /**
     * Kirim ke satu pengguna. $dedupeKey mencegah notifikasi yang sama terkirim dua kali.
     */
    public function to(User|int|null $user, string $title, ?string $body = null, ?string $url = null, ?string $dedupeKey = null): void
    {
        if ($user === null) {
            return;
        }

        $userId = $user instanceof User ? $user->id : $user;
        $data   = ['title' => $title, 'body' => $body, 'url' => $url];

        if ($dedupeKey) {
            UserNotification::firstOrCreate(['user_id' => $userId, 'dedupe_key' => $dedupeKey], $data);

            return;
        }

        UserNotification::create($data + ['user_id' => $userId]);
    }

    /** Kirim ke semua pengguna dengan role tertentu. */
    public function toRole(string|array $roles, string $title, ?string $body = null, ?string $url = null, ?string $dedupeKey = null): void
    {
        User::whereIn('role', (array) $roles)->pluck('id')
            ->each(fn ($id) => $this->to($id, $title, $body, $url, $dedupeKey));
    }
}