<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'event_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'quotation_id' => 'nullable|exists:quotations,id',
            'gst_enabled' => 'boolean',
            'gst_percentage' => 'numeric|min:0|max:100',
            'discount' => 'numeric|min:0',
        ]);

        $order = $this->orderService->createOrder($request->all());

        return response()->json($order->load('items.menuItem', 'customer'), 201);
    }

    public function show($id)
    {
        $order = Order::where('uuid', $id)
            ->orWhere('id', $id)
            ->with('items.menuItem', 'customer', 'branch', 'activities.user')
            ->firstOrFail();

        return response()->json($order);
    }
}
