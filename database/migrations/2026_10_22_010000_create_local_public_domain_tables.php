<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_publik', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->string('google_sub')->unique(); $t->string('email')->unique(); $t->string('nama');
            $t->string('jenis_kelamin')->nullable(); $t->string('nik', 16)->nullable()->index(); $t->string('no_kk', 16)->nullable();
            $t->string('foto_ktp_url')->nullable(); $t->string('foto_kk_url')->nullable(); $t->string('alamat_kabupaten')->nullable();
            $t->string('alamat_kecamatan')->nullable(); $t->string('alamat_kode_pos')->nullable(); $t->text('alamat_lengkap')->nullable(); $t->string('no_hp')->nullable();
            $t->boolean('nama_sesuai_ktp')->default(false); $t->timestamps();
        });
        Schema::create('akun_publik_keluarga', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('akun_id')->index(); $t->string('nik', 16)->index(); $t->string('nama'); $t->string('jenis_kelamin')->nullable(); $t->timestamps();
        });
        Schema::create('balikgratis_pengaturan', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->unsignedInteger('tahun')->unique(); $t->unsignedInteger('kuota')->default(0); $t->string('status')->default('tutup');
            $t->timestamp('dibuka_at')->nullable(); $t->string('dibuka_oleh')->nullable(); $t->timestamp('ditutup_at')->nullable(); $t->string('ditutup_oleh')->nullable(); $t->timestamps();
        });
        Schema::create('balikgratis_pemesanan', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('akun_id')->nullable()->index(); $t->uuid('pemesanan_grup_id')->nullable()->index(); $t->unsignedInteger('tahun')->index();
            $t->string('nomor_tiket')->unique(); $t->string('qr_token')->unique(); $t->string('nama')->nullable(); $t->string('nik', 16)->nullable()->index(); $t->string('kategori')->nullable(); $t->string('jenis_kelamin')->nullable();
            $t->text('alamat')->nullable(); $t->string('status')->default('terdaftar')->index(); $t->string('dokumen_filename')->nullable(); $t->timestamp('verifikasi_at')->nullable(); $t->timestamps();
        });
        Schema::create('trayekwisata_trayek', function (Blueprint $t) { $t->uuid('id')->primary(); $t->string('nama'); $t->text('deskripsi')->nullable(); $t->string('warna')->nullable(); $t->boolean('aktif')->default(true); $t->timestamps(); });
        Schema::create('trayekwisata_titik', function (Blueprint $t) { $t->uuid('id')->primary(); $t->string('nama'); $t->string('jenis')->nullable(); $t->text('deskripsi')->nullable(); $t->decimal('lat', 12, 8)->nullable(); $t->decimal('lng', 12, 8)->nullable(); $t->unsignedInteger('jumlah_kunjungan')->default(0); $t->integer('urutan')->default(0); $t->boolean('aktif')->default(true); $t->timestamps(); });
        Schema::create('trayekwisata_titik_galeri', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('titik_id')->index(); $t->string('tipe'); $t->string('url'); $t->integer('urutan')->default(0); $t->timestamp('created_at')->nullable(); });
        Schema::create('trayekwisata_bus', function (Blueprint $t) { $t->uuid('id')->primary(); $t->string('nama'); $t->string('plat_nomor')->nullable(); $t->unsignedInteger('kapasitas')->default(0); $t->boolean('aktif')->default(true); $t->timestamps(); });
        Schema::create('trayekwisata_jadwal', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('trayek_id')->nullable()->index(); $t->uuid('bus_id')->nullable()->index(); $t->date('tanggal')->nullable()->index(); $t->string('hari')->nullable(); $t->string('jam_berangkat')->nullable(); $t->string('jam_pulang')->nullable(); $t->unsignedInteger('kuota_total')->default(0); $t->unsignedInteger('kuota_terisi')->default(0); $t->string('status')->default('aktif')->index(); $t->timestamps(); });
        Schema::create('trayekwisata_pemesanan', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('jadwal_id')->index(); $t->uuid('profil_id')->index(); $t->unsignedInteger('jumlah_kursi'); $t->string('status')->default('terkonfirmasi')->index(); $t->timestamp('checked_in_at')->nullable(); $t->text('catatan_admin')->nullable(); $t->timestamps(); });
        Schema::create('trayekwisata_pemesanan_nik', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('pemesanan_id')->index(); $t->uuid('jadwal_id')->index(); $t->string('nik', 16)->index(); $t->string('nama'); $t->date('tanggal_trayek')->nullable()->index(); $t->timestamps(); });
        Schema::create('trayekwisata_waitlist', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('jadwal_id')->index(); $t->uuid('profil_id')->index(); $t->timestamps(); $t->unique(['jadwal_id', 'profil_id']); });
        Schema::create('asdp_lokasi', function (Blueprint $t) { $t->string('id')->primary(); $t->string('name'); $t->text('description')->nullable(); $t->decimal('lat', 12, 8)->nullable(); $t->decimal('lng', 12, 8)->nullable(); $t->string('image')->nullable(); $t->timestamp('created')->nullable(); $t->timestamp('updated')->nullable(); });
    }

    public function down(): void
    {
        foreach (['asdp_lokasi','trayekwisata_waitlist','trayekwisata_pemesanan_nik','trayekwisata_pemesanan','trayekwisata_jadwal','trayekwisata_bus','trayekwisata_titik_galeri','trayekwisata_titik','trayekwisata_trayek','balikgratis_pemesanan','balikgratis_pengaturan','akun_publik_keluarga','akun_publik'] as $table) Schema::dropIfExists($table);
    }
};
