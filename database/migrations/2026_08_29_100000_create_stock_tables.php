<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestion du matériel & stock : matériels (cartons, bouteilles, matières
     * premières…), mouvements de stock (achat, sortie vente, retour,
     * production, casse, ajustement) et achats.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 160);
            $table->enum('type', ['carton', 'bouteille', 'matiere_premiere', 'autre'])->default('autre');
            $table->string('unite', 20)->default('pièce'); // pièce, kg, litre, sac, carton…
            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('seuil_alerte', 12, 2)->default(0);
            $table->boolean('consignable')->default(false); // rendable (cartons/bouteilles)
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->enum('type', ['achat', 'sortie_vente', 'retour', 'production', 'casse', 'ajustement']);
            $table->decimal('quantite', 12, 2);              // signé : + entrée, - sortie
            $table->decimal('stock_apres', 12, 2)->nullable();
            $table->string('motif', 255)->nullable();
            $table->string('ref_type', 30)->nullable();      // purchase | sale | production
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();
            $table->index(['material_id', 'id']);
            $table->index(['ref_type', 'ref_id']);
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('fournisseur', 160)->nullable();
            $table->date('date_achat');
            $table->decimal('total', 14, 2)->default(0);
            $table->boolean('pour_production')->default(false);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials');
            $table->decimal('quantite', 12, 2);
            $table->decimal('cout_unitaire', 12, 2)->default(0);
            $table->decimal('sous_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('materials');
    }
};
