<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    /**
     * Resend a notification, optionally with a corrected recipient.
     */
    public function resend(
        Request $request,
        Order $order,
        NotificationLog $notification,
        OrderNotificationService $service,
    ): RedirectResponse {
        $request->validate([
            'recipient' => ['nullable', 'string', 'max:255'],
        ]);

        $newRecipient = $request->filled('recipient') ? $request->input('recipient') : null;

        $log = $service->resend($notification, $newRecipient);

        if ($log->isSent()) {
            return back()->with('success', "Notificación reenviada a {$log->recipient}.");
        }

        return back()->with('error', "Error al reenviar: {$log->error_message}");
    }

    /**
     * Send a new notification manually to a specific channel/recipient.
     */
    public function send(
        Request $request,
        Order $order,
        OrderNotificationService $service,
    ): RedirectResponse {
        $request->validate([
            'channel' => ['required', 'string', 'in:' . implode(',', array_keys(NotificationLog::CHANNELS))],
            'recipient' => ['required', 'string', 'max:255'],
        ]);

        $log = $service->send(
            order: $order,
            channelId: $request->input('channel'),
            event: NotificationLog::EVENT_MANUAL_RESEND,
            recipient: $request->input('recipient'),
        );

        if ($log->isSent()) {
            return back()->with('success', "Notificación enviada por {$log->channelLabel()} a {$log->recipient}.");
        }

        return back()->with('error', "Error al enviar: {$log->error_message}");
    }

    /**
     * Update the order's customer contact info (email/phone).
     */
    public function updateContact(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $order->update($request->only(['customer_email', 'customer_phone']));

        return back()->with('success', 'Datos de contacto actualizados.');
    }
}
