<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('customer.notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        $targetUrl = is_string($notification->data['url'] ?? null)
            ? $notification->data['url']
            : route('customer.notifications.index');

        return redirect($targetUrl);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()
            ->route('customer.notifications.index')
            ->with('status', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}
