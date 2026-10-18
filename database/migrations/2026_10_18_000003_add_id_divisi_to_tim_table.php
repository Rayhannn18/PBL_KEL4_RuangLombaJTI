<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('tim', function (Blueprint $table) {
            $table->unsignedBigInteger('id_divisi')->nullable()->after('id_lomba');
            $table->foreign('id_divisi')->references('id')->on('lomba_divisi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tim', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
            $table->dropColumn('id_divisi');
        });
    }
};
