<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            
            $order = Order::create([
                'branch_id' => $user->branch_id ?? $data['branch_id'],
                'order_number' => $this->generateOrderNumber($data['branch_id'] ?? $user->branch_id),
                'quotation_id' => $data['quotation_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'event_date' => $data['event_date'],
                'food_preparation_start_time' => $data['food_preparation_start_time'] ?? null,
                'food_delivery_time' => $data['food_delivery_time'] ?? null,
                'event_start_time' => $data['event_start_time'] ?? null,
                'gst_enabled' => $data['gst_enabled'] ?? false,
                'gst_percentage' => $data['gst_percentage'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'status' => 'pending',
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = \App\Models\MenuItem::find($item['menu_item_id']);
                $unitPrice = $menuItem->price_per_unit;
                $total = $unitPrice * $item['quantity'];
                $subtotal += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $total,
                ]);
            }

            $gstAmount = $order->gst_enabled ? ($subtotal * $order->gst_percentage / 100) : 0;
            $grandTotal = $subtotal + $gstAmount - $order->discount;

            $order->update([
                'subtotal' => $subtotal,
                'gst_amount' => $gstAmount,
                'grand_total' => $grandTotal,
            ]);

            $this->logActivity($order, 'Created', 'Order created');

            return $order->fresh();
        });
    }

    public function logActivity(Order $order, string $action, string $notes = null): void
    {
        OrderActivity::create([
            'order_id' => $order->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function generateOrderNumber($branchId): string
    {
        $branch = \App\Models\Branch::find($branchId);
        $prefix = $branch->invoice_prefix ?? 'ORD';
        $count = Order::where('branch_id', $branchId)->count() + 1;
        return $prefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
