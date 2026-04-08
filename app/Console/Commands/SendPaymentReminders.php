<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'reminders:payment {--days=7 : Number of days overdue before sending reminder}';

    protected $description = 'Send payment reminders for overdue invoices';

    public function handle(): void
    {
        $daysOverdue = (int) $this->option('days');
        
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('balance_due', '>', 0)
            ->whereDate('created_at', '<=', now()->subDays($daysOverdue))
            ->with('customer')
            ->get();

        $this->info("Found {$invoices->count()} overdue invoices");

        foreach ($invoices as $invoice) {
            $days = $invoice->created_at->diffInDays(now());
            SendPaymentReminder::dispatch($invoice, $days);
            $this->line("Queued reminder for invoice {$invoice->invoice_number}");
        }

        $this->info('Payment reminders queued successfully');
    }
}
