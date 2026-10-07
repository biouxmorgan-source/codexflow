<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lien d'invitation à usage unique : le MJ le transmet comme il veut (pas d'envoi de courriel en V1).
        Schema::create('campaign_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('label')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->default('player');
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['campaign_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_invitations');
    }
};
