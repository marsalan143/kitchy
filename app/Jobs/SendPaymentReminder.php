<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public int $daysOverdue
    ) {}

    public function handle(): void
    {
        $customer = $this->invoice->customer;
        
        if (!$customer->email && !$customer->phone) {
            Log::warning("Payment reminder skipped: Customer {$customer->id} has no email or phone");
            return;
        }

        // Send email reminder
        if ($customer->email) {
            try {
                Mail::raw(
                    "Dear {$customer->name},\n\nYour invoice {$this->invoice->invoice_number} is {$this->daysOverdue} days overdue. Amount due: {$this->invoice->balance_due}.\n\nPlease make payment at your earliest convenience.\n\nThank you.",
                    function ($message) use ($customer) {
                        $message->to($customer->email)
                            ->subject("Payment Reminder - Invoice {$this->invoice->invoice_number}");
                    }
                );
                Log::info("Payment reminder email sent to {$customer->email} for invoice {$this->invoice->invoice_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send payment reminder email: " . $e->getMessage());
            }
        }

        // TODO: Send WhatsApp reminder using Twilio/Meta API
        if ($customer->phone) {
            // Implement WhatsApp sending logic here
            Log::info("Payment reminder WhatsApp would be sent to {$customer->phone} for invoice {$this->invoice->invoice_number}");
        }
    }
}
