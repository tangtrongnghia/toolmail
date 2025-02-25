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
        Schema::table('users', function (Blueprint $table) {
            // Thêm cột mới
            $table->string('unlimitmail_key')->nullable();
            $table->string('muamail_key')->nullable();

            // Đổi tên cột mail_api_key
            $table->renameColumn('mail_api_key', 'dongvanfb_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
