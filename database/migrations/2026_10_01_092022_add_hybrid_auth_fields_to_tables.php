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
            $table->string('google_id')->nullable()->after('password');
            $table->string('line_id')->nullable()->after('google_id');
            // Allow password to be nullable if creating an account purely from OAuth
            $table->string('password')->nullable()->change();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->boolean('is_kicked')->default(false)->after('last_activity');
            $table->boolean('is_shadow')->default(false)->after('is_kicked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'line_id']);
            $table->string('password')->nullable(false)->change();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['is_kicked', 'is_shadow']);
        });
    }
};
