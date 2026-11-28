<?php

namespace App\Notifications;

use App\Enums\SubOrderStatus;
use App\Models\SubOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubOrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SubOrder $subOrder,
        public readonly SubOrderStatus $previousStatus,
        public readonly SubOrderStatus $newStatus,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subOrder->loadMissing(['order', 'seller']);

        $mail = (new MailMessage)
            ->subject("LaraMart — Sipariş Durumu Güncellendi (#{$this->subOrder->order->order_number})")
            ->greeting('Merhaba,')
            ->line(sprintf(
                '#%s numaralı siparişinizdeki %s satıcısına ait paketinizin durumu "%s" olarak güncellendi.',
                $this->subOrder->order->order_number,
                $this->subOrder->seller->store_name,
                $this->newStatus->label()
            ));

        if ($this->subOrder->cargo_company && $this->subOrder->tracking_number) {
            $mail->line(sprintf(
                'Kargo Bilgisi: %s — Takip No: %s',
                $this->subOrder->cargo_company,
                $this->subOrder->tracking_number
            ));
        }

        return $mail
            ->action('Sipariş Detayını Görüntüle', route('customer.orders.show', $this->subOrder->order))
            ->line('Bizi tercih ettiğiniz için teşekkür ederiz.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->subOrder->loadMissing(['order', 'seller']);

        $message = sprintf(
            '#%s siparişinizdeki %s paketi "%s" durumuna geçti.',
            $this->subOrder->order->order_number,
            $this->subOrder->seller->store_name,
            $this->newStatus->label()
        );

        if ($this->subOrder->cargo_company && $this->subOrder->tracking_number) {
            $message .= sprintf(' (%s • %s)', $this->subOrder->cargo_company, $this->subOrder->tracking_number);
        }

        return [
            'title' => 'Sipariş Durumu: '.$this->newStatus->label(),
            'message' => $message,
            'order_id' => $this->subOrder->order_id,
            'order_number' => $this->subOrder->order->order_number,
            'sub_order_id' => $this->subOrder->id,
            'previous_status' => $this->previousStatus->value,
            'status' => $this->newStatus->value,
            'status_label' => $this->newStatus->label(),
            'cargo_company' => $this->subOrder->cargo_company,
            'tracking_number' => $this->subOrder->tracking_number,
            'url' => route('customer.orders.show', $this->subOrder->order, false),
        ];
    }
}
