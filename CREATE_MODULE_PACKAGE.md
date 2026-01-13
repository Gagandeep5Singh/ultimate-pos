# Create Kamal Tec Phone Sale Module Package

## Quick Guide to Create Uploadable Module

### Option 1: Simple ZIP Package (Recommended)

Create a ZIP file containing all files with installation instructions.

### Step 1: Create Folder Structure

```
KamalTecPhoneSale/
├── module.json
├── README.txt
├── Files/
│   ├── app/
│   │   ├── KamalTecSale.php
│   │   ├── KamalTecSaleLine.php
│   │   ├── KamalTecPayment.php
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       ├── KamalTecSaleController.php
│   │   │       ├── KamalTecPaymentController.php
│   │   │       └── KamalTecSaleReportController.php
│   │   └── Exports/
│   │       └── KamalTecSalesExport.php
│   ├── resources/
│   │   └── views/
│   │       └── kamal_tec_sale/
│   │           ├── index.blade.php
│   │           ├── create.blade.php
│   │           ├── edit.blade.php
│   │           ├── show.blade.php
│   │           ├── report.blade.php
│   │           └── partials/
│   │               ├── add_payment.blade.php
│   │               └── edit_payment.blade.php
│   └── database/
│       └── migrations/
│           ├── 2025_01_15_120000_create_kamal_tec_sales_table.php
│           ├── 2025_01_15_120001_create_kamal_tec_sale_lines_table.php
│           ├── 2025_01_15_120002_create_kamal_tec_payments_table.php
│           └── 2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php
├── Install/
│   ├── routes_to_add.txt
│   └── menu_to_add.txt
└── Config/
    └── config.php
```

### Step 2: Create module.json

```json
{
    "name": "KamalTecPhoneSale",
    "alias": "kamaltecsale",
    "description": "Kamal Tec Phone Sale module for recording 3rd-party phone sales with commission tracking",
    "keywords": ["phone", "sale", "commission"],
    "active": 1,
    "order": 0,
    "providers": [],
    "aliases": {},
    "files": [],
    "requires": []
}
```

### Step 3: Create Config/config.php

```php
<?php

return [
    'name' => 'KamalTecPhoneSale',
    'module_version' => '1.0',
];
```

### Step 4: Create README.txt

```
KAMAL TEC PHONE SALE MODULE - INSTALLATION INSTRUCTIONS
========================================================

After uploading this module, follow these steps:

1. COPY FILES:
   - Copy all files from Files/app/ to your main app/ directory
   - Copy all files from Files/resources/views/ to your main resources/views/ directory
   - Copy all files from Files/database/migrations/ to your main database/migrations/ directory

2. ADD ROUTES:
   - Open routes/web.php
   - Add the routes from Install/routes_to_add.txt

3. ADD MENU:
   - Open app/Http/Middleware/AdminSidebarMenu.php
   - Add the menu code from Install/menu_to_add.txt

4. RUN MIGRATIONS:
   php artisan migrate

5. CLEAR CACHE:
   php artisan optimize:clear

6. DONE!
   The module should now be available in your sidebar menu.
```

### Step 5: Create Install Files

#### Install/routes_to_add.txt
```
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

#### Install/menu_to_add.txt
```
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
)->order(50);
```

### Step 6: Copy All Your Files

Copy all the files from your current implementation to the `Files/` directory maintaining the exact structure.

### Step 7: Create ZIP File

1. Select the entire `KamalTecPhoneSale` folder
2. Right-click → "Send to" → "Compressed (zipped) folder"
3. Name it `KamalTecPhoneSale.zip`

### Step 8: Upload

1. Go to "Manage Modules" page
2. Click "Upload Module" button
3. Select `KamalTecPhoneSale.zip`
4. Click "Upload"
5. Follow the installation instructions in README.txt

---

## Quick Script to Create Package

You can use this PowerShell script (run from your project root):

```powershell
# Create module directory structure
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Files\app\Http\Controllers"
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Files\app\Exports"
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Files\resources\views\kamal_tec_sale\partials"
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Files\database\migrations"
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Install"
New-Item -ItemType Directory -Force -Path "KamalTecPhoneSale\Config"

# Copy files (adjust paths as needed)
Copy-Item "app\KamalTecSale.php" "KamalTecPhoneSale\Files\app\"
Copy-Item "app\KamalTecSaleLine.php" "KamalTecPhoneSale\Files\app\"
Copy-Item "app\KamalTecPayment.php" "KamalTecPhoneSale\Files\app\"
Copy-Item "app\Http\Controllers\KamalTecSaleController.php" "KamalTecPhoneSale\Files\app\Http\Controllers\"
Copy-Item "app\Http\Controllers\KamalTecPaymentController.php" "KamalTecPhoneSale\Files\app\Http\Controllers\"
Copy-Item "app\Http\Controllers\KamalTecSaleReportController.php" "KamalTecPhoneSale\Files\app\Http\Controllers\"
Copy-Item "app\Exports\KamalTecSalesExport.php" "KamalTecPhoneSale\Files\app\Exports\"
Copy-Item "resources\views\kamal_tec_sale\*" "KamalTecPhoneSale\Files\resources\views\kamal_tec_sale\" -Recurse
Copy-Item "database\migrations\*kamal_tec*.php" "KamalTecPhoneSale\Files\database\migrations\"

# Create ZIP
Compress-Archive -Path "KamalTecPhoneSale" -DestinationPath "KamalTecPhoneSale.zip" -Force
```

---

**The ZIP file is now ready to upload!**
