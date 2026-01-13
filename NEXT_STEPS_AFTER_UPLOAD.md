# Next Steps After Manual File Upload

## ✅ What You've Already Done
- [x] Files uploaded manually
- [x] Routes added to `routes/web.php`
- [x] Menu items added to `AdminSidebarMenu.php`

## 🔧 What You Need to Do Now

### Step 1: Run Database Migrations

**Via SSH/Terminal:**
```bash
cd /path/to/your/project
php artisan migrate
```

**Or if you get permission errors:**
```bash
php artisan migrate --force
```

This will create the database tables:
- `kamal_tec_sales`
- `kamal_tec_sale_lines`
- `kamal_tec_payments`
- Add `kt_invoice_no` column to `kamal_tec_sales`

### Step 2: Clear All Caches

**Run these commands:**
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

### Step 3: Set File Permissions (if needed)

```bash
chmod -R 755 app/KamalTec*.php
chmod -R 755 app/Http/Controllers/KamalTec*.php
chmod -R 755 app/Exports/KamalTec*.php
chmod -R 755 resources/views/kamal_tec_sale/
chmod -R 755 database/migrations/*kamal_tec*.php
```

### Step 4: Verify Installation

1. **Log out and log back in** (to refresh the menu)
2. **Check the sidebar menu** - You should see "Kamal Tec Phone Sale" with submenu:
   - List
   - Add New
   - Report
3. **Test the module:**
   - Click "List" - should show the sales list page
   - Click "Add New" - should show the create form
   - Try creating a test sale
   - Try adding a payment
   - Check the report

## ✅ Verification Checklist

- [ ] Migrations run successfully
- [ ] All caches cleared
- [ ] Menu appears in sidebar after logout/login
- [ ] Can access `/kamal-tec-sales` (list page)
- [ ] Can access `/kamal-tec-sales/create` (create page)
- [ ] Can create a new sale
- [ ] Can edit a sale
- [ ] Can add payments
- [ ] Can view report
- [ ] Export button works

## 🔍 If Something Doesn't Work

### Menu Not Showing?
- Clear browser cache
- Log out and log back in
- Check if menu code is in the correct location in `AdminSidebarMenu.php`

### Routes Not Working?
- Run: `php artisan route:clear`
- Check: `php artisan route:list | grep kamal-tec`
- Verify routes are inside the authenticated middleware group

### "Class not found" Errors?
- Run: `composer dump-autoload`
- Run: `php artisan optimize:clear`
- Verify all files are in correct locations

### Database Errors?
- Check if migrations ran: `php artisan migrate:status`
- Verify database connection in `.env`
- Check if tables exist in database

## 🎉 You're Almost Done!

Just run the migrations and clear cache, then test the module. Everything should work!
