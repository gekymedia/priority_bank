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
        Schema::table('loans', function (Blueprint $table) {
            // rename columns if they exist
            if (Schema::hasColumn('loans', 'borrower')) {
                $table->renameColumn('borrower', 'borrower_name');
            }
            if (Schema::hasColumn('loans', 'date')) {
                $table->renameColumn('date', 'date_given');
            }
            if (Schema::hasColumn('loans', 'description')) {
                $table->renameColumn('description', 'notes');
            }
            // drop existing status if present
            if (Schema::hasColumn('loans', 'status')) {
                $table->dropColumn('status');
            }
        });
        // Add new columns and recreate status
        Schema::table('loans', function (Blueprint $table) {
            $table->enum('status', ['borrowed', 'returned', 'lost'])->default('borrowed')->after('amount');
            $table->string('borrower_phone')->nullable()->after('borrower_name');
            $table->date('expected_return_date')->nullable()->after('date_given');
            $table->decimal('returned_amount', 12, 2)->default(0)->after('status');
            $table->enum('channel', ['bank', 'momo', 'cash', 'other'])->default('other')->after('returned_amount');
            $table->foreignId('account_id')->nullable()->after('channel')
                ->constrained('accounts')->nullOnDelete();
            // ensure notes is text
            $table->text('notes')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
            $table->dropColumn('channel');
            $table->dropColumn('returned_amount');
            $table->dropColumn('expected_return_date');
            $table->dropColumn('borrower_phone');
            $table->dropColumn('status');
            $table->dropColumn('notes');
            // restore previous enum and columns
            $table->enum('status', ['given', 'repaid'])->default('given');
            $table->string('description')->nullable();
            $table->renameColumn('borrower_name', 'borrower');
            $table->renameColumn('date_given', 'date');
        });
    }
};