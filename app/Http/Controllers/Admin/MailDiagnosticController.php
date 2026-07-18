<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Diagnostic d'envoi d'e-mail, réservé aux administrateurs.
 *
 * Répond à la seule question qui compte quand « aucun mail n'arrive » :
 * la configuration réellement utilisée par le serveur est-elle la bonne, et
 * l'envoi produit-il une erreur ? Sans cet outil, il faut un accès SSH.
 */
class MailDiagnosticController extends Controller
{
    public function index(Request $request)
    {
        $config = [
            'transport' => config('mail.default'),
            'from' => config('mail.from.address').' ('.config('mail.from.name').')',
            'sendmail_path' => config('mail.mailers.sendmail.path'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'true' : 'false',
            'app_name' => config('app.name'),
        ];

        // Un cache de configuration obsolète est la cause la plus fréquente :
        // le .env a bien été modifié, mais Laravel lit encore l'ancien cache.
        $config['config_en_cache'] = file_exists(base_path('bootstrap/cache/config.php')) ? 'oui' : 'non';

        $binaire = explode(' ', (string) config('mail.mailers.sendmail.path'))[0];
        $config['sendmail_present'] = is_executable($binaire) ? 'oui' : 'NON — chemin introuvable';

        $resultat = null;

        if ($request->isMethod('post')) {
            $destinataire = $request->input('email') ?: Auth::user()?->email;

            try {
                Mail::raw(
                    "Ceci est un test d'envoi depuis ".config('app.name').".\n\n"
                    ."Transport : ".config('mail.default')."\n"
                    ."Date : ".now()->format('d/m/Y H:i:s'),
                    fn ($m) => $m->to($destinataire)->subject('Test d\'envoi — '.config('app.name'))
                );

                $resultat = ['ok' => true, 'message' => "E-mail envoyé à {$destinataire}. Vérifiez la boîte de réception ET les indésirables."];
            } catch (\Throwable $e) {
                $resultat = ['ok' => false, 'message' => get_class($e).' : '.$e->getMessage()];
            }
        }

        return response()->view('admin.diagnostic-mail', [
            'config' => $config,
            'resultat' => $resultat,
            'emailParDefaut' => Auth::user()?->email,
        ]);
    }
}
