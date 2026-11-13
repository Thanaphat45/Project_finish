<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('care_instructions', function (Blueprint $table) {
            $table->text('Care_instructions')->change();
        });
    }

    public function down(): void
    {
        Schema::table('care_instructions', function (Blueprint $table) {
            $table->string('Care_instructions', 255)->change();
        });
    }
};
