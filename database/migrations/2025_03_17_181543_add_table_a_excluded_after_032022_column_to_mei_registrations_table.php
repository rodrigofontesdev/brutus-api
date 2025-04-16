<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mei_categories', function (Blueprint $table) {
            $table->boolean('table_a_excluded_after_032022')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('mei_categories', function (Blueprint $table) {
            $table->dropColumn('table_a_excluded_after_032022');
        });
    }
};
