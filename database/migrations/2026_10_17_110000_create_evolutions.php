<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Évolutions prévues pour la plateforme, tenues par l'administrateur.
        Schema::create('evolutions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('detail')->nullable();
            $table->string('status', 20)->default('planned');
            $table->string('priority', 10)->default('normal');
            // Échéance libre : une version, un mois, « avant l'ouverture »…
            $table->string('target', 100)->nullable();
            $table->timestamps();
        });

        DB::table('evolutions')->insert([
            'title' => 'Créer le compte Stripe et activer les paiements',
            'detail' => "Le code est prêt (v0.16.0) : sans clés, aucun bouton de paiement n'apparaît et l'essai fonctionne.\n"
                ."1. Créer le compte sur stripe.com et tester en mode test : produit « Premium », un prix mensuel et un prix annuel.\n"
                ."2. Renseigner STRIPE_SECRET, STRIPE_PRICE_MONTHLY, STRIPE_PRICE_YEARLY et STRIPE_WEBHOOK_SECRET dans le .env.\n"
                ."3. Payer avec la carte de test 4242 4242 4242 4242 et vérifier la formule dans « Comptes ».\n"
                .'4. Pour de vrais paiements : activer le compte avec une structure légale, puis déclarer le webhook /stripe/webhook en production.',
            'status' => 'planned',
            'priority' => 'normal',
            'target' => 'Avant l’ouverture au public',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('evolutions');
    }
};
