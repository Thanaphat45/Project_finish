<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_ADL')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_ADL')->nullable(false)->change();
        });
    }
};
