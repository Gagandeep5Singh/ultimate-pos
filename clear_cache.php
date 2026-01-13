<?php
/**
 * Quick cache clearing script
 * Run this file directly in browser: http://localhost/UltimatePOS/public/clear_cache.php
 * Or via command line: php clear_cache.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Clearing caches...\n";

// Clear application cache
Artisan::call('cache:clear');
echo "✓ Application cache cleared\n";

// Clear route cache
try {
    Artisan::call('route:clear');
    echo "✓ Route cache cleared\n";
} catch (\Exception $e) {
    echo "⚠ Route cache clear skipped: " . $e->getMessage() . "\n";
}

// Clear config cache
try {
    Artisan::call('config:clear');
    echo "✓ Config cache cleared\n";
} catch (\Exception $e) {
    echo "⚠ Config cache clear skipped: " . $e->getMessage() . "\n";
}

// Clear view cache
try {
    Artisan::call('view:clear');
    echo "✓ View cache cleared\n";
} catch (\Exception $e) {
    echo "⚠ View cache clear skipped: " . $e->getMessage() . "\n";
}

echo "\nAll caches cleared! Please refresh your browser.\n";

