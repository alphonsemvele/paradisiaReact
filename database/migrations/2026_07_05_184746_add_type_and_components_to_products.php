<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Type du produit : simple ou composé (nomenclature)
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'type')) {
                $table->enum('type', ['simple', 'compose'])->default('simple')->after('price');
            }
        });

        // Composition d'un produit composé : liste de (produit simple, quantité)
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_composite_product')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->foreignId('id_component_product')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['id_composite_product', 'id_component_product'], 'pc_composite_component_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_components');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
