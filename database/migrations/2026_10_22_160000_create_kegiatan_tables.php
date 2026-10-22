<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('judul');
            $table->foreignId('jenis_kegiatan_id')->constrained('jenis_kegiatan')->restrictOnDelete();
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('belum_mulai')->index();
            $table->string('detail_status')->nullable();
            $table->unsignedTinyInteger('progres')->default(0);
            $table->string('prioritas', 10)->default('sedang');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_target_selesai')->nullable()->index();
            $table->date('tanggal_selesai_aktual')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Tim kegiatan
        Schema::create('kegiatan_user', function (Blueprint $table) {
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->primary(['kegiatan_id', 'user_id']);
        });

        // Riwayat perubahan status/progres/catatan
        Schema::create('kegiatan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('perubahan'); // {"field": {"dari": x, "ke": y}}
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('dinas_luar_kota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->restrictOnDelete();
            $table->string('kota_tujuan');
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->text('keperluan');
            $table->string('lampiran_surat_tugas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dinas_luar_kota_user', function (Blueprint $table) {
            $table->foreignId('dinas_luar_kota_id')->constrained('dinas_luar_kota')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->primary(['dinas_luar_kota_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinas_luar_kota_user');
        Schema::dropIfExists('dinas_luar_kota');
        Schema::dropIfExists('kegiatan_logs');
        Schema::dropIfExists('kegiatan_user');
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('jenis_kegiatan');
    }
};
