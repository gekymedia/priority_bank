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
        Schema::table('expenses', function (Blueprint $table) {
            // Remove obsolete columns
            if (Schema::hasColumn('expenses', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('expenses', 'description')) {
                $table->dropColumn('description');
            }
            // Add new references
            $table->foreignId('expense_category_id')->nullable()->after('user_id')
                ->constrained('expense_categories')->nullOnDelete();
            $table->enum('channel', ['bank', 'momo', 'cash', 'other'])->default('other')->after('amount');
            $table->foreignId('account_id')->nullable()->after('channel')
                ->constrained('accounts')->nullOnDelete();
            $table->text('notes')->nullable()->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
            $table->dropColumn('channel');
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
            $table->dropColumn('notes');
            // restore removed columns
            $table->string('category')->nullable();
            $table->string('description')->nullable();
        });
    }
};