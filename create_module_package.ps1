# PowerShell Script to Create Kamal Tec Phone Sale Module Package
# Run this script from your project root directory

Write-Host "Creating Kamal Tec Phone Sale Module Package..." -ForegroundColor Green

# Create module directory structure
$moduleDir = "KamalTecPhoneSale"
$filesDir = "$moduleDir\Files"

Write-Host "Creating directory structure..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "$moduleDir\Config" | Out-Null
New-Item -ItemType Directory -Force -Path "$moduleDir\Install" | Out-Null
New-Item -ItemType Directory -Force -Path "$filesDir\app\Http\Controllers" | Out-Null
New-Item -ItemType Directory -Force -Path "$filesDir\app\Exports" | Out-Null
New-Item -ItemType Directory -Force -Path "$filesDir\resources\views\kamal_tec_sale\partials" | Out-Null
New-Item -ItemType Directory -Force -Path "$filesDir\database\migrations" | Out-Null

Write-Host "Copying files..." -ForegroundColor Yellow

# Copy Models
Copy-Item "app\KamalTecSale.php" "$filesDir\app\" -ErrorAction SilentlyContinue
Copy-Item "app\KamalTecSaleLine.php" "$filesDir\app\" -ErrorAction SilentlyContinue
Copy-Item "app\KamalTecPayment.php" "$filesDir\app\" -ErrorAction SilentlyContinue

# Copy Controllers
Copy-Item "app\Http\Controllers\KamalTecSaleController.php" "$filesDir\app\Http\Controllers\" -ErrorAction SilentlyContinue
Copy-Item "app\Http\Controllers\KamalTecPaymentController.php" "$filesDir\app\Http\Controllers\" -ErrorAction SilentlyContinue
Copy-Item "app\Http\Controllers\KamalTecSaleReportController.php" "$filesDir\app\Http\Controllers\" -ErrorAction SilentlyContinue

# Copy Export
Copy-Item "app\Exports\KamalTecSalesExport.php" "$filesDir\app\Exports\" -ErrorAction SilentlyContinue

# Copy Views
if (Test-Path "resources\views\kamal_tec_sale") {
    Copy-Item "resources\views\kamal_tec_sale\*" "$filesDir\resources\views\kamal_tec_sale\" -Recurse -Force -ErrorAction SilentlyContinue
}

# Copy Migrations
Get-ChildItem "database\migrations\*kamal_tec*.php" | Copy-Item -Destination "$filesDir\database\migrations\" -ErrorAction SilentlyContinue

Write-Host "Creating module configuration files..." -ForegroundColor Yellow

# Create module.json
$moduleJson = @{
    name = "KamalTecPhoneSale"
    alias = "kamaltecsale"
    description = "Kamal Tec Phone Sale module for recording 3rd-party phone sales with commission tracking"
    keywords = @("phone", "sale", "commission", "kamal tec")
    active = 1
    order = 0
    providers = @()
    aliases = @{}
    files = @()
    requires = @()
} | ConvertTo-Json -Depth 10

$moduleJson | Out-File "$moduleDir\module.json" -Encoding UTF8

# Create Config/config.php
$configPhp = @"
<?php

return [
    'name' => 'KamalTecPhoneSale',
    'module_version' => '1.0',
];
"@
$configPhp | Out-File "$moduleDir\Config\config.php" -Encoding UTF8

# Create Install/routes_to_add.txt
$routesText = @"
// Kamal Tec Phone Sale Routes
Route::resource('kamal-tec-sales', \App\Http\Controllers\KamalTecSaleController::class);
Route::get('kamal-tec-sales-export', [\App\Http\Controllers\KamalTecSaleController::class, 'export'])->name('kamal-tec-sales.export');
Route::get('kamal-tec-sales/{sale_id}/add-payment', [\App\Http\Controllers\KamalTecPaymentController::class, 'addPayment'])->name('kamal-tec-sales.add-payment');
Route::post('kamal-tec-sales/{sale_id}/payments', [\App\Http\Controllers\KamalTecPaymentController::class, 'storePayment'])->name('kamal-tec-sales.store-payment');
Route::get('kamal-tec-payments/{payment_id}/edit', [\App\Http\Controllers\KamalTecPaymentController::class, 'editPayment'])->name('kamal-tec-payments.edit');
Route::put('kamal-tec-payments/{payment_id}', [\App\Http\Controllers\KamalTecPaymentController::class, 'updatePayment'])->name('kamal-tec-payments.update');
Route::delete('kamal-tec-payments/{payment_id}', [\App\Http\Controllers\KamalTecPaymentController::class, 'destroy'])->name('kamal-tec-payments.destroy');
Route::get('kamal-tec-sale-report', [\App\Http\Controllers\KamalTecSaleReportController::class, 'index'])->name('kamal-tec-sale-report');
"@
$routesText | Out-File "$moduleDir\Install\routes_to_add.txt" -Encoding UTF8

# Create Install/menu_to_add.txt
$menuText = @"
//Kamal Tec Phone Sale dropdown
`$menu->dropdown(
    'Kamal Tec Phone Sale',
    function (`$sub) {
        `$sub->url(
            action([\App\Http\Controllers\KamalTecSaleController::class, 'index']),
            __('lang_v1.list'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sales' && request()->segment(2) == null]
        )->order(1);
        `$sub->url(
            action([\App\Http\Controllers\KamalTecSaleController::class, 'create']),
            __('messages.add'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sales' && request()->segment(2) == 'create']
        )->order(2);
        `$sub->url(
            action([\App\Http\Controllers\KamalTecSaleReportController::class, 'index']),
            __('report.reports'),
            ['icon' => '', 'active' => request()->segment(1) == 'kamal-tec-sale-report']
        )->order(3);
    },
    ['icon' => '<i class="fa fa-mobile"></i>', 'active' => in_array(request()->segment(1), ['kamal-tec-sales', 'kamal-tec-sale-report'])]
)->order(50);
"@
$menuText | Out-File "$moduleDir\Install\menu_to_add.txt" -Encoding UTF8

# Create README.txt
$readmeText = @"
KAMAL TEC PHONE SALE MODULE - INSTALLATION INSTRUCTIONS
========================================================

After uploading this module via "Manage Modules" → "Upload Module", 
follow these steps to complete the installation:

1. COPY FILES FROM MODULE TO MAIN APPLICATION:
   
   From: Modules/KamalTecPhoneSale/Files/
   To: Your main application root
   
   - Copy Files/app/* to app/
   - Copy Files/resources/views/* to resources/views/
   - Copy Files/database/migrations/* to database/migrations/

2. ADD ROUTES:
   - Open routes/web.php
   - Find the authenticated routes section
   - Add the routes from Install/routes_to_add.txt

3. ADD MENU ITEMS:
   - Open app/Http/Middleware/AdminSidebarMenu.php
   - Find a good location in the menu function
   - Add the menu code from Install/menu_to_add.txt

4. RUN MIGRATIONS:
   php artisan migrate

5. CLEAR CACHE:
   php artisan optimize:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear

6. VERIFY:
   - Log out and log back in
   - Check sidebar menu for "Kamal Tec Phone Sale"
   - Access /kamal-tec-sales to verify it works

DONE! The module is now installed and ready to use.
"@
$readmeText | Out-File "$moduleDir\README.txt" -Encoding UTF8

Write-Host "Creating ZIP file..." -ForegroundColor Yellow

# Create ZIP file
if (Test-Path "KamalTecPhoneSale.zip") {
    Remove-Item "KamalTecPhoneSale.zip" -Force
}

Compress-Archive -Path $moduleDir -DestinationPath "KamalTecPhoneSale.zip" -Force

Write-Host "`nModule package created successfully!" -ForegroundColor Green
Write-Host "Location: KamalTecPhoneSale.zip" -ForegroundColor Cyan
Write-Host "`nYou can now upload this file via:" -ForegroundColor Yellow
Write-Host "  Manage Modules → Upload Module → Select KamalTecPhoneSale.zip" -ForegroundColor Cyan
Write-Host "`nAfter uploading, follow the instructions in:" -ForegroundColor Yellow
Write-Host "  Modules/KamalTecPhoneSale/README.txt" -ForegroundColor Cyan
