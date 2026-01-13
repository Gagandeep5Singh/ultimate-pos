# Kamal Tec Phone Sale Module - Complete Deployment Package

## 📦 Quick Deployment Summary

**Total Files:** 18 new files + 2 files to modify

---

## 📂 File Structure

```
Your Project Root/
├── app/
│   ├── KamalTecSale.php                    [NEW]
│   ├── KamalTecSaleLine.php                [NEW]
│   ├── KamalTecPayment.php                 [NEW]
│   └── Http/
│       └── Controllers/
│           ├── KamalTecSaleController.php          [NEW]
│           ├── KamalTecPaymentController.php       [NEW]
│           └── KamalTecSaleReportController.php    [NEW]
│   └── Exports/
│       └── KamalTecSalesExport.php         [NEW]
│
├── database/
│   └── migrations/
│       ├── 2025_01_15_120000_create_kamal_tec_sales_table.php              [NEW]
│       ├── 2025_01_15_120001_create_kamal_tec_sale_lines_table.php         [NEW]
│       ├── 2025_01_15_120002_create_kamal_tec_payments_table.php            [NEW]
│       └── 2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php [NEW]
│
├── resources/
│   └── views/
│       └── kamal_tec_sale/
│           ├── index.blade.php             [NEW]
│           ├── create.blade.php            [NEW]
│           ├── edit.blade.php               [NEW]
│           ├── show.blade.php              [NEW]
│           ├── report.blade.php             [NEW]
│           └── partials/
│               ├── add_payment.blade.php    [NEW]
│               └── edit_payment.blade.php   [NEW]
│
├── routes/
│   └── web.php                              [MODIFY - Add routes]
│
└── app/Http/Middleware/
    └── AdminSidebarMenu.php                 [MODIFY - Add menu]
```

---

## 🚀 Deployment Instructions

### Step 1: Upload All New Files

Upload these files maintaining the exact directory structure:

#### Models (3 files)
1. `app/KamalTecSale.php`
2. `app/KamalTecSaleLine.php`
3. `app/KamalTecPayment.php`

#### Controllers (3 files)
4. `app/Http/Controllers/KamalTecSaleController.php`
5. `app/Http/Controllers/KamalTecPaymentController.php`
6. `app/Http/Controllers/KamalTecSaleReportController.php`

#### Export Class (1 file)
7. `app/Exports/KamalTecSalesExport.php`

#### Views (7 files)
8. `resources/views/kamal_tec_sale/index.blade.php`
9. `resources/views/kamal_tec_sale/create.blade.php`
10. `resources/views/kamal_tec_sale/edit.blade.php`
11. `resources/views/kamal_tec_sale/show.blade.php`
12. `resources/views/kamal_tec_sale/report.blade.php`
13. `resources/views/kamal_tec_sale/partials/add_payment.blade.php`
14. `resources/views/kamal_tec_sale/partials/edit_payment.blade.php`

#### Migrations (4 files)
15. `database/migrations/2025_01_15_120000_create_kamal_tec_sales_table.php`
16. `database/migrations/2025_01_15_120001_create_kamal_tec_sale_lines_table.php`
17. `database/migrations/2025_01_15_120002_create_kamal_tec_payments_table.php`
18. `database/migrations/2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php`

---

### Step 2: Modify routes/web.php

**Location:** `routes/web.php`

**Find:** The authenticated routes section (usually inside `Route::middleware(['auth'])->group(function () {`)

**Add this code block:**

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

---

### Step 3: Modify app/Http/Middleware/AdminSidebarMenu.php

**Location:** `app/Http/Middleware/AdminSidebarMenu.php`

**Find:** A good location in the menu (after other menu items, before the closing of the menu function)

**Add this code block:**

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
)->order(50); // Adjust order number as needed
```

---

### Step 4: Run Database Migrations

**Via SSH/Terminal:**

```bash
cd /path/to/your/project
php artisan migrate
```

**Or if you get permission errors:**

```bash
php artisan migrate --force
```

---

### Step 5: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

**If using Composer:**

```bash
composer dump-autoload
```

---

### Step 6: Set File Permissions

```bash
chmod -R 755 app/KamalTec*.php
chmod -R 755 app/Http/Controllers/KamalTec*.php
chmod -R 755 app/Exports/KamalTec*.php
chmod -R 755 resources/views/kamal_tec_sale/
chmod -R 755 database/migrations/*kamal_tec*.php
```

---

## ✅ Verification Steps

1. **Check Routes:**
   ```bash
   php artisan route:list | grep kamal-tec
   ```
   Should show 9 routes.

2. **Check Database Tables:**
   ```sql
   SHOW TABLES LIKE 'kamal_tec%';
   ```
   Should show 3 tables.

3. **Check Menu:**
   - Log in to your application
   - Look for "Kamal Tec Phone Sale" in the sidebar menu
   - Click it to see submenu items

4. **Test Functionality:**
   - Create a new sale
   - Edit a sale
   - Add a payment
   - View report
   - Export sales

---

## 🔧 Troubleshooting

### "Class not found" Error
```bash
composer dump-autoload
php artisan optimize:clear
```

### Routes Not Working
```bash
php artisan route:clear
php artisan route:cache
```

### Menu Not Showing
- Clear browser cache
- Log out and log back in
- Check if menu code is in correct location

### Migration Errors
- Check database connection in `.env`
- Verify migration files are uploaded
- Check if tables already exist

---

## 📋 File Checklist

Use this checklist to ensure all files are uploaded:

**Models (3/3):**
- [ ] app/KamalTecSale.php
- [ ] app/KamalTecSaleLine.php
- [ ] app/KamalTecPayment.php

**Controllers (3/3):**
- [ ] app/Http/Controllers/KamalTecSaleController.php
- [ ] app/Http/Controllers/KamalTecPaymentController.php
- [ ] app/Http/Controllers/KamalTecSaleReportController.php

**Export (1/1):**
- [ ] app/Exports/KamalTecSalesExport.php

**Views (7/7):**
- [ ] resources/views/kamal_tec_sale/index.blade.php
- [ ] resources/views/kamal_tec_sale/create.blade.php
- [ ] resources/views/kamal_tec_sale/edit.blade.php
- [ ] resources/views/kamal_tec_sale/show.blade.php
- [ ] resources/views/kamal_tec_sale/report.blade.php
- [ ] resources/views/kamal_tec_sale/partials/add_payment.blade.php
- [ ] resources/views/kamal_tec_sale/partials/edit_payment.blade.php

**Migrations (4/4):**
- [ ] database/migrations/2025_01_15_120000_create_kamal_tec_sales_table.php
- [ ] database/migrations/2025_01_15_120001_create_kamal_tec_sale_lines_table.php
- [ ] database/migrations/2025_01_15_120002_create_kamal_tec_payments_table.php
- [ ] database/migrations/2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php

**Modified Files (2/2):**
- [ ] routes/web.php (routes added)
- [ ] app/Http/Middleware/AdminSidebarMenu.php (menu added)

**Total: 20 files**

---

## 📞 Support

If you need the actual file contents, all files are available in your local development environment. Copy them from:
- `c:\xampp\htdocs\UltimatePOS\public\` (or your local path)

---

**Last Updated:** 2025-01-20
**Module Version:** 1.0
