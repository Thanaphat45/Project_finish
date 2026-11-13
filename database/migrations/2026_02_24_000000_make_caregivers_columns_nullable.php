<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $strings = [
        'Name_CG','Related','Phone_CG','Name_Elderly','Address','Group_ADL','Disease','Disability','Rights',
        'Consciousness','Vital_signs','Bedsores','Pain','Swelling','Itchy_rash','Stiff_joints','Malnutrition',
        'Eating','Swallowing','Defecation','Urinary_excretion','Taking_medicine','Emotional_state',
        'Economic_problems','Social_problems','Doctor_FU','Other_problems','Assistance','Reporter'
    ];

    public function up(): void
    {
        // Schema::change() (doctrine/dbal) works on both MySQL and PostgreSQL
        Schema::table('care_givers', function (Blueprint $table) {
            foreach ($this->strings as $col) {
                $table->string($col, 255)->nullable()->change();
            }
            $table->date('Birthday')->nullable()->change();
            $table->date('Date_CG')->nullable()->change();
            $table->integer('Weight')->nullable()->change();
            $table->integer('Height')->nullable()->change();
            $table->integer('Waist')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('care_givers', function (Blueprint $table) {
            foreach ($this->strings as $col) {
                $table->string($col, 255)->nullable(false)->change();
            }
            $table->date('Birthday')->nullable(false)->change();
            $table->date('Date_CG')->nullable(false)->change();
            $table->integer('Weight')->nullable(false)->change();
            $table->integer('Height')->nullable(false)->change();
            $table->integer('Waist')->nullable(false)->change();
        });
    }
};
