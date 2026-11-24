<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // A phone-only account has no email, so email must become nullable.
            $table->string('email')->nullable()->change();

            // Normalized E.164 format, e.g. +8801XXXXXXXXX.
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('status', 30)->default('pending_verification')->index()->after('password');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->boolean('is_protected')->default(false)->after('last_login_at');
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement(
                "CREATE TRIGGER users_login_identifier_check_insert BEFORE INSERT ON users
                WHEN NEW.email IS NULL AND NEW.phone IS NULL
                BEGIN SELECT RAISE(ABORT, 'A user must have at least one login identifier.'); END"
            );
            DB::statement(
                "CREATE TRIGGER users_login_identifier_check_update BEFORE UPDATE ON users
                WHEN NEW.email IS NULL AND NEW.phone IS NULL
                BEGIN SELECT RAISE(ABORT, 'A user must have at least one login identifier.'); END"
            );
            DB::statement(
                "CREATE TRIGGER users_status_check_insert BEFORE INSERT ON users
                WHEN NEW.status NOT IN ('active', 'inactive', 'suspended', 'pending_verification')
                BEGIN SELECT RAISE(ABORT, 'Invalid user status.'); END"
            );
            DB::statement(
                "CREATE TRIGGER users_status_check_update BEFORE UPDATE ON users
                WHEN NEW.status NOT IN ('active', 'inactive', 'suspended', 'pending_verification')
                BEGIN SELECT RAISE(ABORT, 'Invalid user status.'); END"
            );
        } else {
            // A user must have at least one login identifier.
            DB::statement(
                'ALTER TABLE users ADD CONSTRAINT users_login_identifier_check CHECK (email IS NOT NULL OR phone IS NOT NULL)'
            );

            // Status must be one of the controlled values (see App\Modules\Identity\Enums\UserStatus).
            DB::statement(
                "ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive', 'suspended', 'pending_verification'))"
            );
        }

        DB::table('users')
            ->where('status', 'pending_verification')
            ->where(function ($query): void {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER users_status_check_update');
            DB::statement('DROP TRIGGER users_status_check_insert');
            DB::statement('DROP TRIGGER users_login_identifier_check_update');
            DB::statement('DROP TRIGGER users_login_identifier_check_insert');
        } else {
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_status_check');
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_login_identifier_check');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'phone_verified_at', 'status', 'last_login_at', 'is_protected']);
            // email is intentionally left nullable: reverting could fail if phone-only users exist.
        });
    }
};
