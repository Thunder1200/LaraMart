<?php

namespace App\Notifications;

use App\Models\SubOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubOrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SubOrder $subOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('LaraMart Sipariş Durumu Güncellendi - #' . $this->subOrder->order->order_number)
            ->greeting('Merhaba ' . $notifiable->name . ',')
            ->line($this->subOrder->seller->store_name . ' mağazasından verdiğiniz alt siparişin durumu güncellendi.')
            ->line('Yeni Durum: ' . $this->subOrder->status->label())
            ->when($this->subOrder->tracking_number, function (MailMessage $mail)
            {
                return $mail->line('Kargo: ' . $this->subOrder->cargo_company . ' - Takip No: ' . $this->subOrder->tracking_number);
            })
            ->action('Siparişimi Görüntüle', url('/hesabim/siparisler/' . $this->subOrder->order_id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->subOrder->order_id,
            'sub_order_id' => $this->subOrder->id,
            'order_number' => $this->subOrder->order->order_number,
            'store_name' => $this->subOrder->seller->store_name,
            'status' => $this->subOrder->status->value,
            'status_label' => $this->subOrder->status->label(),
            'cargo_company' => $this->subOrder->cargo_company,
            'tracking_number' => $this->subOrder->tracking_number,
        ];
    }
}

