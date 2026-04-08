<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:customer,supplier',
            'reference_id' => 'required|exists:' . ($request->type === 'customer' ? 'customers' : 'suppliers') . ',id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank,online',
            'direction' => 'required|in:advance,partial,full',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();

        $payment = Payment::create([
            'branch_id' => $user->branch_id,
            'type' => $request->type,
            'reference_id' => $request->reference_id,
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'method' => $request->method,
            'direction' => $request->direction,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        if ($payment->invoice_id) {
            $this->invoiceService->updateInvoiceBalance($payment->invoice);
        }

        return response()->json($payment->load('invoice', 'customer', 'supplier'), 201);
    }
}
