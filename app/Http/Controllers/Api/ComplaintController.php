<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        // Admin only - requires authentication
        $complaints = Complaint::with('order', 'branch')
            ->latest()
            ->paginate(20);

        return response()->json($complaints);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string',
        ]);

        $order = null;
        if ($request->order_id) {
            $order = \App\Models\Order::find($request->order_id);
        }

        $complaint = Complaint::create([
            'branch_id' => $order?->branch_id,
            'complaint_number' => $this->generateComplaintNumber(),
            'order_id' => $request->order_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'status' => 'open',
        ]);

        return response()->json($complaint, 201);
    }

    private function generateComplaintNumber(): string
    {
        $count = Complaint::count() + 1;
        return 'COMP-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
