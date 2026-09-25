<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Adresse e-mail recevant les notifications admin. */
class NotificationRecipient extends Model
{
    protected $fillable = ['email', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    /**
     * Liste des e-mails actifs à notifier. Repli sur le superadmin configuré
     * si la liste est vide ou si la table n'existe pas encore (avant migration).
     *
     * @return array<int, string>
     */
    public static function emailsActifs(): array
    {
        try {
            $emails = static::where('actif', true)->pluck('email')->all();
        } catch (\Throwable $e) {
            $emails = [];
        }

        if (empty($emails)) {
            $defaut = config('services.superadmin_email');

            return $defaut ? [$defaut] : [];
        }

        return $emails;
    }
}
