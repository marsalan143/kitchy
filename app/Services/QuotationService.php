<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            
            $quotation = Quotation::create([
                'branch_id' => $user->branch_id ?? $data['branch_id'],
                'quotation_number' => $this->generateQuotationNumber($data['branch_id'] ?? $user->branch_id),
                'revision_number' => 1,
                'customer_id' => $data['customer_id'],
                'event_date' => $data['event_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'gst_enabled' => $data['gst_enabled'] ?? false,
                'gst_percentage' => $data['gst_percentage'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'status' => 'draft',
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $menuItem = \App\Models\MenuItem::find($item['menu_item_id']);
                $unitPrice = $menuItem->price_per_unit;
                $total = $unitPrice * $item['quantity'];
                $subtotal += $total;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total' => $total,
                ]);
            }

            $gstAmount = $quotation->gst_enabled ? ($subtotal * $quotation->gst_percentage / 100) : 0;
            $grandTotal = $subtotal + $gstAmount - $quotation->discount;

            $quotation->update([
                'subtotal' => $subtotal,
                'gst_amount' => $gstAmount,
                'grand_total' => $grandTotal,
            ]);

            return $quotation->fresh();
        });
    }

    public function createRevision(Quotation $parentQuotation, array $data): Quotation
    {
        return DB::transaction(function () use ($parentQuotation, $data) {
            $newRevision = $parentQuotation->replicate();
            $newRevision->revision_number = $parentQuotation->revision_number + 1;
            $newRevision->parent_quotation_id = $parentQuotation->id;
            $newRevision->status = 'draft';
            $newRevision->save();

            foreach ($parentQuotation->items as $item) {
                $newRevision->items()->create($item->toArray());
            }

            return $newRevision;
        });
    }

    private function generateQuotationNumber($branchId): string
    {
        $branch = \App\Models\Branch::find($branchId);
        $prefix = $branch->invoice_prefix ?? 'QT';
        $count = Quotation::where('branch_id', $branchId)->count() + 1;
        return $prefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
