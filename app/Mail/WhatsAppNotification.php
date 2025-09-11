<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WhatsAppNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $type;
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type; // e.g., sendImage or sendLinkPreview
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        if ($this->type === 'paymentSuccess') {
            $who = $this->data['participant']['name'] ?? ($this->data['participant']['email'] ?? 'Peserta');
            $inv = $this->data['transaction']['invoice'] ?? '';
            $subject = '[Payment Success] ' . trim($inv . ' ' . $who);
        } else {
            $label = $this->type === 'sendImage' ? 'Image' : 'Link Preview';
            $subject = '[WA] ' . $label . ' sent to ' . ($this->data['chatId'] ?? 'unknown');
        }

        return $this->subject($subject)
            ->view('emails.whatsapp_notification');
    }
}
