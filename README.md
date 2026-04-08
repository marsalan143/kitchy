# Kitchy - Catering Services Management Platform

A comprehensive Laravel + Filament v3 application for managing catering services with Admin Panel, API-first architecture, Mobile App readiness, and Frontend Website readiness.

## Tech Stack

- **Laravel** (Latest Stable)
- **Filament v3** (Admin Panel)
- **MySQL** (Database)
- **Laravel Sanctum** (API Authentication)
- **Spatie Permissions** (Roles + Feature Permissions)
- **DomPDF** (PDF Generation)
- **Twilio SDK** (WhatsApp API abstraction)
- **Laravel Mail** (Email notifications)
- **Queues & Scheduler** (Cron jobs for automated reminders)

## Features

### Core Architecture
- ✅ API-FIRST design (ALL data exposed via APIs)
- ✅ Filament used ONLY for Admin Panel
- ✅ Frontend website & Mobile App consume APIs
- ✅ UUIDs for API-facing models
- ✅ Business logic in Services / Actions
- ✅ Single source of truth for calculations
- ✅ Activity & audit data preserved

### Multi-User Support
- **Roles**: Admin, Branch Manager, Staff
- Multiple Admins, Managers, and Staff allowed
- Users assigned roles via Spatie Permissions
- Each user belongs to ONE branch
- Admin can access ALL branches
- Branch Manager & Staff restricted to own branch only

### Role-Based Feature Toggles
Fine-grained feature control using Spatie Permissions:
- Staff: Can create quotations & orders, cannot delete orders/invoices, cannot approve discounts
- Branch Manager: Can approve discounts, confirm orders, view branch-level reports
- Admin: Can override menu prices, override discounts, delete & restore records, access all reports & branches

### Branch Handling
- `branch_id` REQUIRED on ALL business tables
- `branch_id` NEVER shown in any UI
- `branch_id` ALWAYS auto-assigned internally
- Managers & Staff: Auto-scoped to branch, no branch selector
- Admin: Sees ALL branches by default, has branch filters on ALL listings, has GLOBAL DASHBOARD BRANCH SWITCHER

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL
- Node.js & NPM (for frontend assets)

### Setup Steps

1. **Clone/Download the project**
   ```bash
   cd kitchy
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` file with your database credentials:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kitchy
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Access the admin panel**
   - URL: `http://localhost:8000/admin`
   - Default Admin: `admin@kitchy.com` / `password`

## Database Structure

### Core Tables
- `branches` - Branch management (Admin only)
- `users` - User accounts with branch assignment
- `menu_items` - Menu items with pricing
- `customers` - Customer management
- `suppliers` - Supplier management
- `supplier_products` - Supplier product catalog

### Business Tables
- `quotations` - Quotation system with versioning
- `quotation_items` - Quotation line items
- `orders` - Order management
- `order_items` - Order line items
- `order_activities` - Order activity log (audit trail)
- `invoices` - Invoice management (soft delete enabled)
- `payments` - Payment tracking (customer & supplier)
- `complaints` - Complaint management

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout (requires auth)

### Menu
- `GET /api/menu-items` - List menu items
- `GET /api/categories` - List categories

### Quotations
- `POST /api/quotations` - Create quotation
- `GET /api/quotations/{id}` - Get quotation details

### Orders
- `POST /api/orders` - Create order
- `GET /api/orders/{id}` - Get order details

### Invoices
- `GET /api/invoices/{id}` - Get invoice details
- `GET /api/invoices/{id}/download` - Download invoice PDF

### Payments
- `POST /api/payments` - Create payment

### Complaints
- `POST /api/complaints` - Submit complaint (PUBLIC)
- `GET /api/complaints` - List complaints (ADMIN)

## Automated Reminders

### Payment Reminders
Runs daily to send reminders for overdue invoices:
```bash
php artisan reminders:payment --days=7
```

### Event Reminders
Runs daily to send reminders before scheduled events:
```bash
php artisan reminders:event --days=1
```

### Schedule in Cron
Add to your `crontab`:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Or add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('reminders:payment --days=7')->daily();
    $schedule->command('reminders:event --days=1')->daily();
}
```

## Permissions

### Admin
- Full access to all features
- Can manage branches
- Can override prices and discounts
- Can delete and restore records
- Can view all branches

### Branch Manager
- Can approve discounts
- Can confirm orders
- Can view branch-level reports
- Cannot delete orders or invoices
- Cannot override prices

### Staff
- Can create quotations & orders
- Cannot delete orders or invoices
- Cannot apply or approve discounts
- Cannot override prices

## Filament Admin Panel

### Access
- URL: `/admin`
- Login with your user credentials

### Features
- Branch management (Admin only)
- Menu item management
- Customer management
- Supplier management
- Quotation management with versioning
- Order management with activity logs
- Invoice management with PDF download
- Payment tracking
- Complaint management
- Dashboard with KPI widgets

### Branch Switching (Admin Only)
Admins have a global branch switcher on the dashboard to filter data across all branches.

## Services

### QuotationService
- `createQuotation(array $data)` - Create new quotation
- `createRevision(Quotation $parent, array $data)` - Create quotation revision

### OrderService
- `createOrder(array $data)` - Create new order
- `logActivity(Order $order, string $action, string $notes)` - Log order activity

### InvoiceService
- `createInvoiceFromOrder(Order $order)` - Create invoice from order
- `updateInvoiceBalance(Invoice $invoice)` - Update invoice balance after payment

## Jobs & Queues

### SendPaymentReminder
Sends payment reminders via email and WhatsApp for overdue invoices.

### SendEventReminder
Sends event reminders via email and WhatsApp before scheduled events.

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Queue Worker
```bash
php artisan queue:work
```

## Configuration

### Sanctum
Configured in `config/sanctum.php`. API tokens are used for authentication.

### Permissions
Configured in `config/permission.php`. Roles and permissions are managed via Spatie.

### Activity Log
Configured in `config/activitylog.php`. Tracks all model changes.

## Notes

- All API-facing models use UUIDs
- Branch scoping is enforced via global scopes
- Soft deletes enabled on: Orders, Invoices, Payments, Customers, Suppliers
- Activity logs preserved for audit trail
- WhatsApp integration requires Twilio/Meta API configuration

## License

MIT License

## Support

For issues and questions, please contact the development team.
