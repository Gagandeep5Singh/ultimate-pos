# Kamal Tec Phone Sale Module - Deployment Guide

This guide contains all files and instructions needed to deploy the Kamal Tec Phone Sale module to your production server.

## 📋 Table of Contents
1. [Files to Upload](#files-to-upload)
2. [Database Migrations](#database-migrations)
3. [Deployment Steps](#deployment-steps)
4. [Verification](#verification)

---

## 📁 Files to Upload

### 1. Models (app/Models or app/)
- `app/KamalTecSale.php`
- `app/KamalTecSaleLine.php`
- `app/KamalTecPayment.php`

### 2. Controllers (app/Http/Controllers/)
- `app/Http/Controllers/KamalTecSaleController.php`
- `app/Http/Controllers/KamalTecPaymentController.php`
- `app/Http/Controllers/KamalTecSaleReportController.php`

### 3. Export Class (app/Exports/)
- `app/Exports/KamalTecSalesExport.php`

### 4. Views (resources/views/kamal_tec_sale/)
- `resources/views/kamal_tec_sale/index.blade.php`
- `resources/views/kamal_tec_sale/create.blade.php`
- `resources/views/kamal_tec_sale/edit.blade.php`
- `resources/views/kamal_tec_sale/show.blade.php`
- `resources/views/kamal_tec_sale/report.blade.php`
- `resources/views/kamal_tec_sale/partials/add_payment.blade.php`
- `resources/views/kamal_tec_sale/partials/edit_payment.blade.php`

### 5. Database Migrations (database/migrations/)
- `database/migrations/2025_01_15_120000_create_kamal_tec_sales_table.php`
- `database/migrations/2025_01_15_120001_create_kamal_tec_sale_lines_table.php`
- `database/migrations/2025_01_15_120002_create_kamal_tec_payments_table.php`
- `database/migrations/2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php`

### 6. Routes (routes/web.php)
- Add the routes section (see Routes Section below)

### 7. Middleware (app/Http/Middleware/AdminSidebarMenu.php)
- Add menu items (see Menu Section below)

---

## 🔧 Files to Modify

### routes/web.php
Add these routes inside the authenticated middleware group:

```php
// Kamal Tec Phone Sale Routes
Route::resource('kamal-tec-sales', \App\Http\Controllers\KamalTecSaleController::class);
Route::get('kamal-tec-sales-export', [\App\Http\Controllers\KamalTecSaleController::class, 'export'])->name('kamal-tec-sales.export');
Route::get('kamal-tec-sales/{sale_id}/add-payment', [\App\Http\Controllers\KamalTecPaymentController::class, 'addPayment'])->name('kamal-tec-sales.add-payment');
Route::post('kamal-tec-sales/{sale_id}/payments', [\App\Http\Controllers\KamalTecPaymentController::class, 'storePayment'])->name('kamal-tec-sales.store-payment');
Route::get('kamal-tec-payments/{payment_id}/edit', [\App\Http\Controllers\KamalTecPaymentController::class, 'editPayment'])->name('kamal-tec-payments.edit');
Route::put('kamal-tec-payments/{payment_id}', [\App\Http\Controllers\KamalTecPaymentController::class, 'updatePayment'])->name('kamal-tec-payments.update');
Route::delete('kamal-tec-payments/{payment_id}', [\App\Http\Controllers\KamalTecPaymentController::class, 'destroy'])->name('kamal-tec-payments.destroy');
Route::get('kamal-tec-sale-report', [\App\Http\Controllers\KamalTecSaleReportController::class, 'index'])->name('kamal-tec-sale-report');
```

### app/Http/Middleware/AdminSidebarMenu.php
Add this menu section (find a good location, typically after other menu items):

```php
//Kamal Tec Phone Sale dropdown
$menu->dropdown(
    'Kamal Tec Phone Sale',
    function ($sub) {
        $sub->url(
            action([\App\Http\Controllers\KamalTecSaleController::class, 'index']),
            __('lang_v1.list'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sales' && request()->segment(2) == null]
        )->order(1);
        $sub->url(
            action([\App\Http\Controllers\KamalTecSaleController::class, 'create']),
            __('messages.add'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sales' && request()->segment(2) == 'create']
        )->order(2);
        $sub->url(
            action([\App\Http\Controllers\KamalTecSaleReportController::class, 'index']),
            __('report.reports'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sale-report']
        )->order(3);
    },
    ['icon' => '<i class="fa fa-mobile"></i>', 'active' => in_array(request()->segment(1), ['kamal-tec-sales', 'kamal-tec-sale-report'])]
)->order(XX); // Set appropriate order number
```

---

## 🗄️ Database Migrations

### Step 1: Upload Migration Files
Upload all 4 migration files to `database/migrations/` directory.

### Step 2: Run Migrations
SSH into your server and run:

```bash
cd /path/to/your/project
php artisan migrate
```

**Important:** If you get an error about a migration already existing (like `battery_percentage`), you can skip it:
```bash
php artisan migrate --force
```

---

## 📦 Deployment Steps

### Step 1: Backup Your Production Database
```bash
# Create a backup before making changes
mysqldump -u username -p database_name > backup_before_kamal_tec.sql
```

### Step 2: Upload Files via FTP/SFTP
Upload all files maintaining the directory structure:
- Models → `app/`
- Controllers → `app/Http/Controllers/`
- Export → `app/Exports/`
- Views → `resources/views/kamal_tec_sale/`
- Migrations → `database/migrations/`

### Step 3: Modify Existing Files
1. **routes/web.php**: Add the routes section (see above)
2. **app/Http/Middleware/AdminSidebarMenu.php**: Add the menu section (see above)

### Step 4: Set File Permissions
```bash
# Set proper permissions
chmod -R 755 app/Http/Controllers/KamalTec*.php
chmod -R 755 app/KamalTec*.php
chmod -R 755 app/Exports/KamalTec*.php
chmod -R 755 resources/views/kamal_tec_sale/
chmod -R 755 database/migrations/*kamal_tec*.php
```

### Step 5: Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 6: Run Migrations
```bash
php artisan migrate
```

### Step 7: Verify Routes
```bash
php artisan route:list | grep kamal-tec
```

You should see all the kamal-tec routes listed.

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Menu item "Kamal Tec Phone Sale" appears in sidebar
- [ ] Can access `/kamal-tec-sales` (list page)
- [ ] Can create a new sale (`/kamal-tec-sales/create`)
- [ ] Can edit a sale (`/kamal-tec-sales/{id}/edit`)
- [ ] Can view sale details (`/kamal-tec-sales/{id}`)
- [ ] Can add payments to a sale
- [ ] Can access report (`/kamal-tec-sale-report`)
- [ ] Export button works (`/kamal-tec-sales-export`)
- [ ] Database tables created:
  - [ ] `kamal_tec_sales`
  - [ ] `kamal_tec_sale_lines`
  - [ ] `kamal_tec_payments`
  - [ ] `kt_invoice_no` column exists in `kamal_tec_sales`

---

## 🔍 Troubleshooting

### Issue: "Class not found" errors
**Solution:** Clear autoload cache:
```bash
composer dump-autoload
```

### Issue: Routes not working
**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Views not found
**Solution:** Clear view cache:
```bash
php artisan view:clear
```

### Issue: Migration errors
**Solution:** Check if tables already exist:
```bash
php artisan migrate:status
```

### Issue: Menu not showing
**Solution:** 
1. Check if menu code is in the correct location in `AdminSidebarMenu.php`
2. Clear all caches
3. Log out and log back in

---

## 📝 Notes

1. **Permissions**: Ensure your web server user has write permissions to `storage/` and `bootstrap/cache/` directories.

2. **Composer**: If you're using Composer, you may need to run:
   ```bash
   composer dump-autoload
   ```

3. **Environment**: Make sure your `.env` file has correct database credentials.

4. **Backup**: Always backup your database before running migrations in production.

5. **Testing**: Test in a staging environment first if possible.

---

## 📞 Support

If you encounter any issues during deployment:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Verify all files are uploaded correctly
4. Ensure database connection is working

---

**Last Updated:** 2025-01-20
**Module Version:** 1.0
