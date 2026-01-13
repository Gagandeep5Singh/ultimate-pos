KAMAL TEC PHONE SALE MODULE - INSTALLATION INSTRUCTIONS
========================================================

After uploading this module via "Manage Modules" â†’ "Upload Module", 
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
