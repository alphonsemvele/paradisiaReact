<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lieu de livraison renseigné par le client lors d'une commande passée
     * via un lien partagé. Nullable : les commandes existantes (retrait en
     * point de vente) n'en ont pas.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'delivery_location')) {
                $table->string('delivery_location', 500)->nullable()->after('customer_phone');
            }
            // Origine de la commande : boutique, lien partagé…
            if (! Schema::hasColumn('sales', 'channel')) {
                $table->string('channel', 30)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['delivery_location', 'channel']);
        });
    }
};
