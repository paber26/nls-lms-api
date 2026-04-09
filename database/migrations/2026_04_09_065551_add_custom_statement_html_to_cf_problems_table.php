<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cf_problems', function (Blueprint $table) {
            $table->longText('custom_statement_html')->nullable()->after('statement_html');
            $table->boolean('is_custom_statement')->default(false)->after('custom_statement_html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cf_problems', function (Blueprint $table) {
            $table->dropColumn(['custom_statement_html', 'is_custom_statement']);
        });
    }
};
