<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show($id)
    {
        $invoice = Invoice::where('uuid', $id)
            ->orWhere('id', $id)
            ->with('customer', 'order', 'payments')
            ->firstOrFail();

        return response()->json($invoice);
    }

    public function download($id)
    {
        $invoice = Invoice::where('uuid', $id)
            ->orWhere('id', $id)
            ->with('customer', 'order.items.menuItem')
            ->firstOrFail();

        // TODO: Implement PDF generation using DomPDF
        return response()->json(['message' => 'PDF download not yet implemented']);
    }
}
