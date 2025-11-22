<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ==== Identitas tambahan ====
            $table->string('username')->nullable()->unique()->after('name');

            // ==== Info karyawan / organisasi ====
            $table->string('employee_code')->nullable()->index()->after('password'); // NIK/Badge
            $table->string('phone')->nullable()->index()->after('employee_code');
            $table->string('company')->nullable()->after('phone');      // ABN/AAP/ABC
            $table->string('department')->nullable()->after('company'); // produksi/qa/logistik/dll
            $table->string('position')->nullable()->after('department');
            $table->string('site')->nullable()->after('position');
            $table->date('join_date')->nullable()->after('site');

            // ==== Status akses ====
            $table->enum('status', ['active', 'inactive', 'suspended'])
                  ->default('active')
                  ->index()
                  ->after('role');

            // ==== Profil tambahan ====
            $table->string('avatar_path')->nullable()->after('status');
            $table->text('notes')->nullable()->after('avatar_path');

            // ==== Security / tracking login ====
            $table->timestamp('last_login_at')->nullable()->after('notes');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            // ==== Audit siapa yang create/update ====
            $table->unsignedBigInteger('created_by')->nullable()->index()->after('last_login_ip');
            $table->unsignedBigInteger('updated_by')->nullable()->index()->after('created_by');

            // ==== Soft delete ====
            $table->softDeletes()->after('updated_at'); // adds deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // drop unique dulu sebelum drop kolom
            $table->dropUnique(['username']);

            $table->dropColumn([
                'username',
                'employee_code',
                'phone',
                'company',
                'department',
                'position',
                'site',
                'join_date',
                'status',
                'avatar_path',
                'notes',
                'last_login_at',
                'last_login_ip',
                'created_by',
                'updated_by',
                'deleted_at',
            ]);
        });
    }
};
