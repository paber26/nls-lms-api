<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$service = new \App\Services\CodeforcesService();
try {
    echo substr($service->getProblemStatementHtml(1213, 'B'), 0, 500);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
