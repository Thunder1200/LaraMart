<?php

namespace App\Mail;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Seller $seller) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LaraMart Satıcı Başvurunuz Onaylandı - '.$this->seller->store_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seller-approved',
        );
    }
}
