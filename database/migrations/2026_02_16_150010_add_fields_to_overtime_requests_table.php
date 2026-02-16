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
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('overtime_requests', 'description')) {
                $table->text('description')->nullable()->after('hours');
            }
            if (!Schema::hasColumn('overtime_requests', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('overtime_requests', 'computed_minutes')) {
                $table->integer('computed_minutes')->nullable()->after('attachment_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'computed_minutes')) {
                $table->dropColumn('computed_minutes');
            }
            if (Schema::hasColumn('overtime_requests', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
            if (Schema::hasColumn('overtime_requests', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
