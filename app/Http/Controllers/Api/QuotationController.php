<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuotationService;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationService $quotationService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'event_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'gst_enabled' => 'boolean',
            'gst_percentage' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        $quotation = $this->quotationService->createQuotation($request->all());

        return response()->json($quotation->load('items.menuItem', 'customer'), 201);
    }

    public function show($id)
    {
        $quotation = Quotation::where('uuid', $id)
            ->orWhere('id', $id)
            ->with('items.menuItem', 'customer', 'branch')
            ->firstOrFail();

        return response()->json($quotation);
    }
}
