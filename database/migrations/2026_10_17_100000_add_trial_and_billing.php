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
            // L'essai commence quand le compte possède sa première campagne (il devient MJ).
            $table->timestamp('trial_started_at')->nullable();
            // Abonnement Stripe : identifiants seulement, aucune donnée de paiement.
            $table->string('stripe_customer_id')->nullable()->unique();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('subscription_status', 30)->nullable();
        });

        // Les comptes déjà MJ commencent leur essai à leur première campagne.
        DB::statement('update users set trial_started_at = (select min(created_at) from campaigns where campaigns.user_id = users.id)');

        // Événements Stripe déjà traités : un webhook rejoué ne s'applique qu'une fois.
        Schema::create('stripe_events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('type', 100);
            $table->timestamp('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_events');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trial_started_at', 'stripe_customer_id', 'stripe_subscription_id', 'subscription_status']);
        });
    }
};
