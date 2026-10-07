<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /** Ouvre une notification : elle est marquée lue, puis on suit son lien. */
    public function open(string $notification): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $url = (string) ($notification->data['url'] ?? '');

        // Seulement des adresses de l'application.
        return str_starts_with($url, url('/').'/') ? redirect()->to($url) : redirect()->route('notifications.index');
    }
}
