# Creating Kamal Tec Phone Sale Module Package

## Overview

The Kamal Tec Phone Sale module needs to be packaged as a ZIP file that can be uploaded through the "Manage Modules" interface. Since the module is currently integrated into the main application, we'll create a module package that includes an installer.

## Module Structure

Create a folder structure like this:

```
KamalTecPhoneSale/
├── module.json
├── composer.json
├── Config/
│   └── config.php
├── Providers/
│   └── KamalTecServiceProvider.php
├── Database/
│   └── Migrations/
│       ├── 2025_01_15_120000_create_kamal_tec_sales_table.php
│       ├── 2025_01_15_120001_create_kamal_tec_sale_lines_table.php
│       ├── 2025_01_15_120002_create_kamal_tec_payments_table.php
│       └── 2025_01_20_120000_add_kt_invoice_no_to_kamal_tec_sales_table.php
├── Install/
│   └── InstallController.php
└── README.md
```

## Step-by-Step Instructions

### Step 1: Create Module Directory

Create a folder named `KamalTecPhoneSale` (or `KamalTecPhoneSale.zip` for the package).

### Step 2: Create Required Files

#### module.json
```json
{
    "name": "KamalTecPhoneSale",
    "alias": "kamaltecsale",
    "description": "Kamal Tec Phone Sale module for recording 3rd-party phone sales with commission tracking",
    "keywords": ["phone", "sale", "commission", "kamal tec"],
    "active": 1,
    "order": 0,
    "providers": [
        "Modules\\KamalTecPhoneSale\\Providers\\KamalTecServiceProvider"
    ],
    "aliases": {},
    "files": [],
    "requires": []
}
```

#### composer.json
```json
{
    "name": "ultimatepos/kamal-tec-phone-sale",
    "description": "Kamal Tec Phone Sale Module",
    "type": "module",
    "license": "MIT",
    "require": {}
}
```

#### Config/config.php
```php
<?php

return [
    'name' => 'KamalTecPhoneSale',
    'module_version' => '1.0',
    'pid' => null,
];
```

### Step 3: Create Service Provider

#### Providers/KamalTecServiceProvider.php
```php
<?php

namespace Modules\KamalTecPhoneSale\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class KamalTecServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot()
    {
        // Publish migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        // Run installer on module activation
        $this->runInstaller();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        //
    }

    /**
     * Run installer to copy files to main app
     */
    private function runInstaller()
    {
        // This will be handled by the InstallController
    }
}
```

### Step 4: Create Install Controller

#### Install/InstallController.php
```php
<?php

namespace Modules\KamalTecPhoneSale\Install;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallController
{
    public static function install()
    {
        // Copy models
        self::copyFiles('app', [
            'KamalTecSale.php',
            'KamalTecSaleLine.php',
            'KamalTecPayment.php'
        ]);

        // Copy controllers
        self::copyFiles('app/Http/Controllers', [
            'KamalTecSaleController.php',
            'KamalTecPaymentController.php',
            'KamalTecSaleReportController.php'
        ]);

        // Copy export
        self::copyFiles('app/Exports', [
            'KamalTecSalesExport.php'
        ]);

        // Copy views
        self::copyDirectory('resources/views/kamal_tec_sale', 'resources/views/kamal_tec_sale');

        // Add routes to web.php
        self::addRoutes();

        // Add menu items
        self::addMenuItems();

        // Run migrations
        Artisan::call('migrate', ['--force' => true]);

        return true;
    }

    private static function copyFiles($destination, $files)
    {
        $modulePath = base_path('Modules/KamalTecPhoneSale');
        $destPath = base_path($destination);

        if (!File::exists($destPath)) {
            File::makeDirectory($destPath, 0755, true);
        }

        foreach ($files as $file) {
            $source = $modulePath . '/Files/' . $destination . '/' . $file;
            $dest = $destPath . '/' . $file;
            
            if (File::exists($source)) {
                File::copy($source, $dest);
            }
        }
    }

    private static function copyDirectory($source, $destination)
    {
        $modulePath = base_path('Modules/KamalTecPhoneSale');
        $sourcePath = $modulePath . '/Files/' . $source;
        $destPath = base_path($destination);

        if (File::exists($sourcePath)) {
            File::copyDirectory($sourcePath, $destPath);
        }
    }

    private static function addRoutes()
    {
        // Routes will be added via a separate file that gets included
    }

    private static function addMenuItems()
    {
        // Menu items will be added via a separate file
    }
}
```

## Alternative: Simple File Package

Since the module system expects a specific structure, the **simplest approach** is to create a ZIP file with all files in their correct structure, and provide manual installation instructions.

## Recommended Approach

**Create a ZIP file with this structure:**

```
KamalTecPhoneSale.zip
└── KamalTecPhoneSale/
    ├── module.json
    ├── composer.json
    ├── Config/
    │   └── config.php
    ├── Providers/
    │   └── KamalTecServiceProvider.php
    ├── Database/
    │   └── Migrations/
    │       └── [all 4 migration files]
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
    │   └── resources/
    │       └── views/
    │           └── kamal_tec_sale/
    │               └── [all view files]
    └── Install/
        ├── routes.txt (routes to add)
        └── menu.txt (menu code to add)
```

## Installation Instructions for Users

After uploading the module:

1. **Extract/Copy Files:**
   - Copy all files from `Files/` directory to the main application
   - Maintain directory structure

2. **Add Routes:**
   - Open `routes/web.php`
   - Add routes from `Install/routes.txt`

3. **Add Menu:**
   - Open `app/Http/Middleware/AdminSidebarMenu.php`
   - Add menu code from `Install/menu.txt`

4. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

5. **Clear Cache:**
   ```bash
   php artisan optimize:clear
   ```

## Creating the ZIP File

1. Create the folder structure above
2. Copy all your files to the `Files/` directory maintaining structure
3. Create the module.json, composer.json, and Config files
4. Zip the entire `KamalTecPhoneSale` folder
5. Upload via "Manage Modules" → "Upload Module"

---

**Note:** The module upload system extracts to `../Modules/` directory. The module will appear in the modules list but may need manual file copying unless you create a proper installer.
