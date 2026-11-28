<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestDecidedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ReturnRequest $returnRequest) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->returnRequest->loadMissing(['orderItem.subOrder.order', 'orderItem.subOrder.seller']);
        $order = $this->returnRequest->orderItem->subOrder->order;
        $approved = $this->returnRequest->status === 'approved';

        $statusText = $approved ? 'Onaylandı' : 'Reddedildi';

        return (new MailMessage)
            ->subject("LaraMart — İade Talebiniz {$statusText} (#{$order->order_number})")
            ->greeting('Merhaba,')
            ->line(sprintf(
                '"%s" ürünü için açtığınız iade talebi %s satıcısı tarafından %s.',
                $this->returnRequest->orderItem->product_name,
                $this->returnRequest->orderItem->subOrder->seller->store_name,
                mb_strtolower($statusText)
            ))
            ->when($approved, fn (MailMessage $mail): MailMessage => $mail->line(
                'İade Tutarı: '.number_format((float) $this->returnRequest->refund_amount, 2, ',', '.').' TL'
            ))
            ->action('Sipariş Detayını Görüntüle', route('customer.orders.show', $order));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->returnRequest->loadMissing(['orderItem.subOrder.order', 'orderItem.subOrder.seller']);
        $order = $this->returnRequest->orderItem->subOrder->order;
        $approved = $this->returnRequest->status === 'approved';
        $statusText = $approved ? 'Onaylandı' : 'Reddedildi';

        return [
            'title' => "İade Talebiniz {$statusText}",
            'message' => sprintf(
                '"%s" ürünü için iade talebiniz %s (%s TL).',
                $this->returnRequest->orderItem->product_name,
                mb_strtolower($statusText),
                number_format((float) $this->returnRequest->refund_amount, 2, ',', '.')
            ),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'return_request_id' => $this->returnRequest->id,
            'decision' => $this->returnRequest->status,
            'url' => route('customer.orders.show', $order, false),
        ];
    }
}
