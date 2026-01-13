# Kamal Tec Phone Sale Module - Upload Instructions

## ✅ Module Package Created!

The module package `KamalTecPhoneSale.zip` has been created successfully in your project root directory.

## 📦 What's Included

The ZIP file contains:
- All model files (KamalTecSale, KamalTecSaleLine, KamalTecPayment)
- All controller files (3 controllers)
- Export class
- All view files (7 blade templates)
- All migration files (4 migrations)
- Installation instructions
- Routes and menu code to add

## 🚀 Upload Steps

### Step 1: Upload the Module

1. Log in to your production server as **superadmin**
2. Navigate to: **Manage Modules** (in admin menu)
3. Click the **"↑ Upload Module"** button
4. Select the file: `KamalTecPhoneSale.zip`
5. Click **"Upload"**
6. Wait for the upload to complete

### Step 2: Complete Installation

After uploading, the module will be extracted to: `../Modules/KamalTecPhoneSale/`

**Now follow these steps:**

#### A. Copy Files to Main Application

Copy files from the module to your main application:

```bash
# Via SSH/Terminal (adjust paths as needed)
cp -r Modules/KamalTecPhoneSale/Files/app/* app/
cp -r Modules/KamalTecPhoneSale/Files/resources/views/* resources/views/
cp Modules/KamalTecPhoneSale/Files/database/migrations/* database/migrations/
```

**OR via FTP:**
- Copy `Modules/KamalTecPhoneSale/Files/app/` contents → `app/`
- Copy `Modules/KamalTecPhoneSale/Files/resources/views/` contents → `resources/views/`
- Copy `Modules/KamalTecPhoneSale/Files/database/migrations/` contents → `database/migrations/`

#### B. Add Routes

1. Open `routes/web.php`
2. Find the authenticated routes section (inside `Route::middleware(['auth'])->group(function () {`)
3. Add the routes from: `Modules/KamalTecPhoneSale/Install/routes_to_add.txt`

#### C. Add Menu Items

1. Open `app/Http/Middleware/AdminSidebarMenu.php`
2. Find a good location in the menu function (after other menu items)
3. Add the menu code from: `Modules/KamalTecPhoneSale/Install/menu_to_add.txt`

#### D. Run Migrations

```bash
php artisan migrate
```

#### E. Clear All Caches

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

If using Composer:
```bash
composer dump-autoload
```

#### F. Set Permissions

```bash
chmod -R 755 app/KamalTec*.php
chmod -R 755 app/Http/Controllers/KamalTec*.php
chmod -R 755 app/Exports/KamalTec*.php
chmod -R 755 resources/views/kamal_tec_sale/
chmod -R 755 database/migrations/*kamal_tec*.php
```

### Step 3: Verify Installation

1. **Log out and log back in** (to refresh menu)
2. Check sidebar menu for **"Kamal Tec Phone Sale"**
3. Click it to see submenu items:
   - List
   - Add
   - Reports
4. Test creating a new sale
5. Test adding a payment
6. Test the report

## 📋 Quick Checklist

- [ ] Module ZIP uploaded via "Manage Modules"
- [ ] Files copied from `Modules/KamalTecPhoneSale/Files/` to main app
- [ ] Routes added to `routes/web.php`
- [ ] Menu code added to `AdminSidebarMenu.php`
- [ ] Migrations run (`php artisan migrate`)
- [ ] Cache cleared
- [ ] Permissions set
- [ ] Module appears in sidebar menu
- [ ] Can create/edit/view sales
- [ ] Can add payments
- [ ] Report works
- [ ] Export works

## 🔍 Troubleshooting

### Module doesn't appear in menu
- Clear all caches
- Log out and log back in
- Check if menu code was added correctly

### Routes not working
- Verify routes were added to `routes/web.php`
- Run `php artisan route:clear`
- Check `php artisan route:list | grep kamal-tec`

### Files not found errors
- Verify all files were copied correctly
- Check file permissions
- Run `composer dump-autoload`

### Database errors
- Verify migrations ran successfully
- Check database connection
- Verify tables exist: `kamal_tec_sales`, `kamal_tec_sale_lines`, `kamal_tec_payments`

## 📁 File Locations After Upload

After uploading, files will be in:
- Module files: `../Modules/KamalTecPhoneSale/`
- Installation files: `../Modules/KamalTecPhoneSale/Install/`
- README: `../Modules/KamalTecPhoneSale/README.txt`

## 🎉 Success!

Once all steps are completed, your Kamal Tec Phone Sale module will be fully functional on your production server!

---

**Need Help?** Check the README.txt file in the module directory for detailed instructions.
