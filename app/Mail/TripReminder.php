<?php

namespace App\Mail;

use App\Models\Itinerary;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TripReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Itinerary $itinerary,
        public int $daysUntil,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysUntil === 1
            ? 'Besok berangkat! — Trip ke '.$this->itinerary->destination
            : '3 hari lagi! — Trip ke '.$this->itinerary->destination;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trip-reminder',
            with: [
                'itinerary' => $this->itinerary,
                'daysUntil' => $this->daysUntil,
                'userName' => $this->itinerary->user->name,
            ],
        );
    }
}
