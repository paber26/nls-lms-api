<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('tryout', function (Blueprint $table) {
    if (!Schema::hasColumn('tryout', 'access_key_info')) {
        $table->text('access_key_info')->nullable()->after('access_key');
        echo "Added access_key_info column.\n";
    } else {
        echo "Column access_key_info already exists.\n";
    }
});
