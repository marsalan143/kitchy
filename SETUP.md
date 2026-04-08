# Quick Setup Guide

## Initial Setup

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Configure database**
   - Update `.env` with your MySQL credentials
   - Create database: `CREATE DATABASE kitchy;`

3. **Run migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Create storage link**
   ```bash
   php artisan storage:link
   ```

5. **Start development server**
   ```bash
   php artisan serve
   ```

6. **Access admin panel**
   - URL: `http://localhost:8000/admin`
   - Email: `admin@kitchy.com`
   - Password: `password`

## Default Credentials

After seeding:
- **Admin User**: `admin@kitchy.com` / `password`
- **Default Branch**: "Main Branch" (auto-created)

## Next Steps

1. Create additional branches (Admin only)
2. Add menu items
3. Create customers
4. Start creating quotations and orders

## Queue Setup

For automated reminders to work:

1. **Start queue worker**
   ```bash
   php artisan queue:work
   ```

2. **Or use supervisor** (production)
   - Configure supervisor to run `php artisan queue:work`

## Cron Setup

Add to crontab for scheduled reminders:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## API Testing

Use Postman or similar tool to test API endpoints:

1. **Login**
   ```bash
   POST /api/login
   {
     "email": "admin@kitchy.com",
     "password": "password"
   }
   ```

2. **Use token in headers**
   ```
   Authorization: Bearer {token}
   ```

## Troubleshooting

### Database Connection Error
- Check `.env` database credentials
- Ensure MySQL is running
- Verify database exists

### Permission Errors
- Run `php artisan permission:cache-reset`
- Re-seed: `php artisan db:seed --class=RolePermissionSeeder`

### Queue Not Working
- Check queue connection in `.env` (default: `sync` for development)
- Start queue worker: `php artisan queue:work`
