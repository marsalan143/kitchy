<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Models\MenuItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderActivity;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Complaint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /** UUID for tables that require it (works even if Eloquent model events are disabled). */
    private function uuid(): string
    {
        return (string) Str::uuid();
    }

    public function run(): void
    {
        // Create additional branches
        $branch1 = Branch::first(); // Main Branch (already exists)
        $branch2 = Branch::create([
            'name' => 'Downtown Branch',
            'phone' => '9876543210',
            'address' => '123 Main Street, Downtown',
            'invoice_prefix' => 'DT',
            'status' => true,
        ]);
        $branch3 = Branch::create([
            'name' => 'Uptown Branch',
            'phone' => '9876543211',
            'address' => '456 Park Avenue, Uptown',
            'invoice_prefix' => 'UP',
            'status' => true,
        ]);

        // Create additional users
        $branchManager1 = User::create([
            'name' => 'John Manager',
            'email' => 'manager@kitchy.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch1->id,
        ]);
        $branchManager1->assignRole('Branch Manager');

        $staff1 = User::create([
            'name' => 'Jane Staff',
            'email' => 'staff@kitchy.com',
            'password' => Hash::make('password'),
            'branch_id' => $branch1->id,
        ]);
        $staff1->assignRole('Staff');

        // Create menu items
        $menuItems = [
            ['name' => 'Chicken Biryani', 'category' => 'Rice', 'unit_type' => 'plate', 'price_per_unit' => 250.00, 'status' => true],
            ['name' => 'Mutton Biryani', 'category' => 'Rice', 'unit_type' => 'plate', 'price_per_unit' => 350.00, 'status' => true],
            ['name' => 'Chicken Curry', 'category' => 'Curry', 'unit_type' => 'plate', 'price_per_unit' => 180.00, 'status' => true],
            ['name' => 'Butter Chicken', 'category' => 'Curry', 'unit_type' => 'plate', 'price_per_unit' => 220.00, 'status' => true],
            ['name' => 'Naan Bread', 'category' => 'Bread', 'unit_type' => 'piece', 'price_per_unit' => 25.00, 'status' => true],
            ['name' => 'Roti', 'category' => 'Bread', 'unit_type' => 'piece', 'price_per_unit' => 15.00, 'status' => true],
            ['name' => 'Gulab Jamun', 'category' => 'Dessert', 'unit_type' => 'piece', 'price_per_unit' => 30.00, 'status' => true],
            ['name' => 'Ice Cream', 'category' => 'Dessert', 'unit_type' => 'plate', 'price_per_unit' => 80.00, 'status' => true],
            ['name' => 'Catering Package (50 people)', 'category' => 'Package', 'unit_type' => 'person', 'price_per_unit' => 500.00, 'status' => true],
            ['name' => 'Catering Package (100 people)', 'category' => 'Package', 'unit_type' => 'person', 'price_per_unit' => 450.00, 'status' => true],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create(array_merge($item, [
                'branch_id' => $branch1->id,
                'uuid' => $this->uuid(),
            ]));
        }

        $pakistaniCustomerNames = [
            'Muhammad Ali',
            'Ahmed Raza',
            'Usman Tariq',
            'Bilal Hussain',
            'Hassan Javed',
            'Sana Khan',
            'Ayesha Noor',
            'Fatima Zahra',
            'Zainab Iqbal',
            'Hira Bibi',
            'Saad Malik',
            'Hamza Aslam',
            'Umar Farooq',
            'Mariam Sheikh',
            'Rabia Akhtar',
        ];

        $pakistaniSupplierNames = [
            'Karachi Fresh Foods',
            'Lahore Spice Traders',
            'Punjab Meat Suppliers',
            'Islamabad Catering Goods',
            'Peshawar Dry Fruit House',
        ];

        // Create customers
        $customers = [];
        for ($i = 1; $i <= 15; $i++) {
            $customerName = $pakistaniCustomerNames[$i - 1] ?? "Customer $i";
            $customerSlug = strtolower(str_replace(' ', '.', $customerName));
            $customers[] = Customer::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'name' => $customerName,
                'phone' => '9876543' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => "{$customerSlug}@example.pk",
                'address' => "House {$i}, Block " . chr(64 + (($i - 1) % 5) + 1) . ', Lahore, Pakistan',
                'opening_balance' => rand(-5000, 5000),
                'credit_limit' => rand(10000, 50000),
            ]);
        }

        // Create suppliers
        $suppliers = [];
        for ($i = 1; $i <= 5; $i++) {
            $supplierName = $pakistaniSupplierNames[$i - 1] ?? "Supplier $i";
            $supplierSlug = strtolower(str_replace(' ', '.', $supplierName));
            $suppliers[] = Supplier::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'name' => $supplierName,
                'phone' => '9876543' . str_pad($i + 100, 3, '0', STR_PAD_LEFT),
                'email' => "{$supplierSlug}@example.pk",
                'address' => "Warehouse {$i}, Industrial Area, Karachi, Pakistan",
                'opening_balance' => rand(-10000, 0),
                'status' => true,
            ]);
        }

        // Create quotations
        for ($i = 1; $i <= 10; $i++) {
            $customer = $customers[array_rand($customers)];
            $quotation = Quotation::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'quotation_number' => 'QT-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'revision_number' => 1,
                'customer_id' => $customer->id,
                'event_date' => Carbon::now()->addDays(rand(1, 30)),
                'subtotal' => 0,
                'gst_enabled' => rand(0, 1),
                'gst_percentage' => 18,
                'gst_amount' => 0,
                'discount' => rand(0, 1000),
                'grand_total' => 0,
                'status' => ['draft', 'sent', 'approved', 'rejected'][rand(0, 3)],
                'expiry_date' => Carbon::now()->addDays(rand(7, 30)),
            ]);

            // Add quotation items
            $selectedItems = MenuItem::where('branch_id', $branch1->id)->inRandomOrder()->take(rand(2, 5))->get();
            $subtotal = 0;
            foreach ($selectedItems as $menuItem) {
                $quantity = rand(1, 10);
                $total = $menuItem->price_per_unit * $quantity;
                $subtotal += $total;
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price_per_unit,
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
        }

        // Create orders
        $orders = [];
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers[array_rand($customers)];
            $quotation = Quotation::where('status', 'approved')->inRandomOrder()->first();
            
            $order = Order::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'order_number' => 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'quotation_id' => $quotation?->id,
                'customer_id' => $customer->id,
                'event_date' => Carbon::now()->addDays(rand(-5, 30)),
                'food_preparation_start_time' => Carbon::now()->addDays(rand(1, 30))->setTime(rand(8, 12), 0),
                'food_delivery_time' => Carbon::now()->addDays(rand(1, 30))->setTime(rand(13, 18), 0),
                'event_start_time' => Carbon::now()->addDays(rand(1, 30))->setTime(rand(18, 22), 0),
                'delivery_status' => ['not_delivered', 'partially_delivered', 'fully_delivered'][rand(0, 2)],
                'subtotal' => 0,
                'gst_enabled' => rand(0, 1),
                'gst_percentage' => 18,
                'gst_amount' => 0,
                'discount' => rand(0, 2000),
                'grand_total' => 0,
                'status' => ['pending', 'confirmed', 'completed', 'cancelled'][rand(0, 3)],
            ]);

            // Add order items
            $selectedItems = MenuItem::where('branch_id', $branch1->id)->inRandomOrder()->take(rand(2, 6))->get();
            $subtotal = 0;
            foreach ($selectedItems as $menuItem) {
                $quantity = rand(1, 15);
                $total = $menuItem->price_per_unit * $quantity;
                $subtotal += $total;
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price_per_unit,
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

            // Add order activities
            $activities = [
                ['action' => 'Created', 'notes' => 'Order created'],
            ];
            
            if ($order->status === 'confirmed') {
                $activities[] = ['action' => 'Confirmed', 'notes' => 'Order confirmed by manager'];
            }
            
            if ($order->status === 'completed') {
                $activities[] = ['action' => 'Confirmed', 'notes' => 'Order confirmed'];
                $activities[] = ['action' => 'Completed', 'notes' => 'Order completed'];
            }

            foreach ($activities as $activity) {
                OrderActivity::create([
                    'order_id' => $order->id,
                    'action' => $activity['action'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    'notes' => $activity['notes'],
                    'created_at' => Carbon::now()->subDays(rand(0, 10)),
                ]);
            }

            $orders[] = $order;
        }

        // Create invoices
        $completedOrders = Order::where('status', 'completed')->orWhere('status', 'confirmed')->get();
        foreach ($completedOrders as $order) {
            $invoice = Invoice::create([
                'uuid' => $this->uuid(),
                'branch_id' => $order->branch_id,
                'invoice_number' => 'INV-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'subtotal' => $order->subtotal,
                'gst_amount' => $order->gst_amount,
                'discount' => $order->discount,
                'paid_amount' => 0,
                'balance_due' => $order->grand_total,
                'status' => 'unpaid',
            ]);

            // Create some payments
            if (rand(0, 1)) {
                $paymentAmount = rand(1000, (int)$invoice->balance_due);
                $payment = Payment::create([
                    'uuid' => $this->uuid(),
                    'branch_id' => $invoice->branch_id,
                    'type' => 'customer',
                    'reference_id' => $invoice->customer_id,
                    'invoice_id' => $invoice->id,
                    'amount' => $paymentAmount,
                    'method' => ['cash', 'bank', 'online'][rand(0, 2)],
                    'direction' => $paymentAmount >= $invoice->balance_due ? 'full' : 'partial',
                    'payment_date' => Carbon::now()->subDays(rand(0, 15)),
                    'notes' => 'Payment received',
                ]);

                $invoice->update([
                    'paid_amount' => $payment->amount,
                    'balance_due' => max(0, $invoice->balance_due - $payment->amount),
                    'status' => $payment->amount >= $invoice->balance_due ? 'paid' : 'partial',
                ]);
            }
        }

        // Create some unpaid invoices (for pending dues widget)
        for ($i = 1; $i <= 5; $i++) {
            $customer = $customers[array_rand($customers)];
            $order = $orders[array_rand($orders)];
            
            Invoice::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'invoice_number' => 'INV-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'subtotal' => rand(5000, 50000),
                'gst_amount' => rand(500, 5000),
                'discount' => rand(0, 2000),
                'paid_amount' => 0,
                'balance_due' => rand(5000, 50000),
                'status' => 'unpaid',
                'created_at' => Carbon::now()->subDays(rand(10, 60)), // Overdue invoices
            ]);
        }

        // Create complaints
        for ($i = 1; $i <= 8; $i++) {
            $order = Order::inRandomOrder()->first();
            Complaint::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'complaint_number' => 'COMP-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'order_id' => rand(0, 1) ? $order->id : null,
                'name' => "Complainant $i",
                'phone' => '9876543' . str_pad($i + 200, 3, '0', STR_PAD_LEFT),
                'email' => "complainant$i@example.com",
                'message' => "This is a sample complaint message $i. We are not satisfied with the service.",
                'status' => ['open', 'in_progress', 'resolved', 'closed'][rand(0, 3)],
            ]);
        }

        // Create supplier payments
        for ($i = 1; $i <= 10; $i++) {
            $supplier = $suppliers[array_rand($suppliers)];
            Payment::create([
                'uuid' => $this->uuid(),
                'branch_id' => $branch1->id,
                'type' => 'supplier',
                'reference_id' => $supplier->id,
                'invoice_id' => null,
                'amount' => rand(5000, 30000),
                'method' => ['cash', 'bank', 'online'][rand(0, 2)],
                'direction' => ['advance', 'partial', 'full'][rand(0, 2)],
                'payment_date' => Carbon::now()->subDays(rand(0, 30)),
                'notes' => "Payment to supplier $i",
            ]);
        }

        $this->command->info('Dummy data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Branch::count() . ' Branches');
        $this->command->info('- ' . User::count() . ' Users');
        $this->command->info('- ' . MenuItem::count() . ' Menu Items');
        $this->command->info('- ' . Customer::count() . ' Customers');
        $this->command->info('- ' . Supplier::count() . ' Suppliers');
        $this->command->info('- ' . Quotation::count() . ' Quotations');
        $this->command->info('- ' . Order::count() . ' Orders');
        $this->command->info('- ' . Invoice::count() . ' Invoices');
        $this->command->info('- ' . Payment::count() . ' Payments');
        $this->command->info('- ' . Complaint::count() . ' Complaints');
    }
}
