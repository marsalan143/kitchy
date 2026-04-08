<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        $customer = $this->order->customer;
        
        if (!$customer->email && !$customer->phone) {
            Log::warning("Event reminder skipped: Customer {$customer->id} has no email or phone");
            return;
        }

        $eventDate = $this->order->event_date->format('F j, Y');
        
        // Send email reminder
        if ($customer->email) {
            try {
                Mail::raw(
                    "Dear {$customer->name},\n\nThis is a reminder that your event is scheduled for {$eventDate}.\n\nOrder Number: {$this->order->order_number}\nEvent Date: {$eventDate}\n\nWe look forward to serving you!\n\nThank you.",
                    function ($message) use ($customer, $eventDate) {
                        $message->to($customer->email)
                            ->subject("Event Reminder - {$eventDate}");
                    }
                );
                Log::info("Event reminder email sent to {$customer->email} for order {$this->order->order_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send event reminder email: " . $e->getMessage());
            }
        }

        // TODO: Send WhatsApp reminder using Twilio/Meta API
        if ($customer->phone) {
            // Implement WhatsApp sending logic here
            Log::info("Event reminder WhatsApp would be sent to {$customer->phone} for order {$this->order->order_number}");
        }
    }
}
