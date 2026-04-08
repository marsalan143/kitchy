<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function createInvoiceFromOrder(Order $order): Invoice
    {
        return DB::transaction(function () use ($order) {
            $invoice = Invoice::create([
                'branch_id' => $order->branch_id,
                'invoice_number' => $this->generateInvoiceNumber($order->branch_id),
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'subtotal' => $order->subtotal,
                'gst_amount' => $order->gst_amount,
                'discount' => $order->discount,
                'paid_amount' => 0,
                'balance_due' => $order->grand_total,
                'status' => 'unpaid',
            ]);

            return $invoice;
        });
    }

    public function updateInvoiceBalance(Invoice $invoice): void
    {
        $paidAmount = $invoice->payments()->sum('amount');
        $balanceDue = $invoice->subtotal + $invoice->gst_amount - $invoice->discount - $paidAmount;

        $status = 'unpaid';
        if ($balanceDue <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $invoice->update([
            'paid_amount' => $paidAmount,
            'balance_due' => max(0, $balanceDue),
            'status' => $status,
        ]);
    }

    private function generateInvoiceNumber($branchId): string
    {
        $branch = \App\Models\Branch::find($branchId);
        $prefix = $branch->invoice_prefix ?? 'INV';
        $count = Invoice::where('branch_id', $branchId)->count() + 1;
        return $prefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
