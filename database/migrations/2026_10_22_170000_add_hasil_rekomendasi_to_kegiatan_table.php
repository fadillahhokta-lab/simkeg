<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->text('hasil')->nullable()->after('catatan');
            $table->text('rekomendasi')->nullable()->after('hasil');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn(['hasil', 'rekomendasi']);
        });
    }
};
