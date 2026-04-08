<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminder;
use App\Models\Order;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:event {--days=1 : Number of days before event to send reminder}';

    protected $description = 'Send event reminders before scheduled events';

    public function handle(): void
    {
        $daysBefore = (int) $this->option('days');
        
        $orders = Order::whereIn('status', ['pending', 'confirmed'])
            ->whereDate('event_date', '=', now()->addDays($daysBefore))
            ->with('customer')
            ->get();

        $this->info("Found {$orders->count()} upcoming events");

        foreach ($orders as $order) {
            SendEventReminder::dispatch($order);
            $this->line("Queued reminder for order {$order->order_number}");
        }

        $this->info('Event reminders queued successfully');
    }
}
