<?php
// Simple test to see if export works
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../bootstrap/app.php';

use App\Exports\ProductPricesWithStockExport;
use Maatwebsite\Excel\Facades\Excel;

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Test the export
try {
    $business_id = 1; // Change to your business ID
    $export = new ProductPricesWithStockExport($business_id);
    
    echo "<h2>Export Test</h2>";
    echo "Headings: " . implode(', ', $export->headings()) . "<br>";
    
    $data = $export->array();
    echo "Data rows: " . count($data) . "<br>";
    
    if (count($data) > 0) {
        echo "First product: " . $data[0][0] . "<br>";
        echo "Sample row: " . implode(', ', $data[0]) . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}