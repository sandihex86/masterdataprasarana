<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->foreignId('api_client_id')->nullable()->after('tokenable_id')->constrained('api_clients')->nullOnDelete();
            $table->index(['api_client_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropIndex(['api_client_id', 'expires_at']);
            $table->dropConstrainedForeignId('api_client_id');
        });
    }
};
