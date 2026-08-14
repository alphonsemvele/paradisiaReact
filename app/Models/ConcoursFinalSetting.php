<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcoursFinalSetting extends Model
{
    protected $fillable = ['corrige'];

    protected $casts = ['corrige' => 'array'];

    public static function actuel(): self
    {
        return static::firstOrCreate([], ['corrige' => self::defaut()]);
    }

    /** Corrigé par défaut (modifiable ensuite par l'admin). */
    public static function defaut(): array
    {
        return [
            ['q' => 'Quel fruit tropical a des piques dehors et est sucré dedans ?', 'r' => 'Ananas'],
            ['q' => 'Rouge dedans, verte dehors, avec des petits points noirs à l\'intérieur. Qui suis-je ?', 'r' => 'Pastèque'],
            ['q' => 'Chez Paradisia on presse du bonheur en bouteille. Comment s\'appelle notre boisson ?', 'r' => 'Paradisia'],
            ['q' => 'Quel fruit est le meilleur pour s\'hydrater à 40°C ?', 'r' => 'Pastèque'],
            ['q' => 'Quel jus accompagne le mieux un bon barbecue le dimanche ?', 'r' => 'Gingembre'],
            ['q' => 'Je suis sucré, rouge et on me met dans les jus et les salades. Qui suis-je ?', 'r' => 'Fraise'],
            ['q' => 'Combien de fruits minimum dans un jus « cocktail » ?', 'r' => '3'],
            ['q' => 'Quel fruit est jaune, long et donne beaucoup d\'énergie ?', 'r' => 'Banane'],
            ['q' => 'Quel agrume sert à faire de la limonade ?', 'r' => 'Citron'],
            ['q' => 'Le gingembre dans un jus sert surtout à quoi ?', 'r' => 'Relever le goût / piquant'],
        ];
    }
}
