<?php

namespace App\Console\Commands;

use App\Mail\TripReminder;
use App\Models\Itinerary;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTripReminders extends Command
{
    protected $signature = 'trusaba:send-reminders';

    protected $description = 'Send trip reminders to travellers before departure';

    public function handle(): int
    {
        $today = Carbon::today();
        $threeDaysLater = $today->copy()->addDays(3);
        $oneDayLater = $today->copy()->addDay();

        // Find confirmed itineraries starting in 3 days or 1 day
        $itineraries = Itinerary::whereIn('status', ['confirmed', 'ongoing'])
            ->whereDate('start_date', '>=', $today)
            ->where(function ($query) use ($threeDaysLater, $oneDayLater) {
                $query->whereDate('start_date', $threeDaysLater)
                    ->orWhereDate('start_date', $oneDayLater);
            })
            ->with('user')
            ->get();

        $sent = 0;
        foreach ($itineraries as $itinerary) {
            $daysUntil = (int) $today->diffInDays(Carbon::parse($itinerary->start_date));
            $subject = $daysUntil === 1
                ? 'Besok berangkat! — Trip ke '.$itinerary->destination
                : '3 hari lagi! — Trip ke '.$itinerary->destination;

            try {
                Mail::to($itinerary->user->email)
                    ->send(new TripReminder($itinerary, $daysUntil));

                // Create reminder record
                $itinerary->reminders()->create([
                    'user_id' => $itinerary->user_id,
                    'remind_at' => now(),
                    'type' => 'email',
                    'message' => $subject,
                    'is_sent' => true,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for itinerary #{$itinerary->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} trip reminders.");

        return self::SUCCESS;
    }
}
