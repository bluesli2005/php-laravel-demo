<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('welcome_messages', function (Blueprint $table) {
            $table->string('page')->default('home')->unique();
        });

        // The original seed inserted id=1 explicitly, leaving PostgreSQL's sequence behind.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select("SELECT setval(
                pg_get_serial_sequence('welcome_messages', 'id'),
                COALESCE((SELECT MAX(id) FROM welcome_messages), 1),
                EXISTS (SELECT 1 FROM welcome_messages)
            )");
        }
    }

    public function down(): void
    {
        Schema::table('welcome_messages', function (Blueprint $table) {
            $table->dropColumn('page');
        });
    }
};
