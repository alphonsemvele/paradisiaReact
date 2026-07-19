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
            'journal' => $this->journalMail(),
        ]);
    }

    /**
     * Dernières traces d'e-mail du journal Laravel.
     *
     * Avec MAIL_MAILER=log, le message entier y est écrit au lieu d'être
     * envoyé : c'est le seul endroit où retrouver ce qui aurait dû partir.
     * En cas d'échec d'envoi, l'erreur exacte s'y trouve aussi.
     *
     * @return array<int, array{date:string, niveau:string, texte:string}>
     */
    private function journalMail(): array
    {
        $fichier = storage_path('logs/laravel.log');

        if (! is_readable($fichier)) {
            return [];
        }

        // On ne lit que la fin du fichier : un journal de plusieurs centaines
        // de Mo ne doit jamais être chargé en mémoire.
        $taille = filesize($fichier);
        $fenetre = 400 * 1024;

        $handle = fopen($fichier, 'rb');
        fseek($handle, max(0, $taille - $fenetre));
        $contenu = (string) fread($handle, $fenetre);
        fclose($handle);

        // Découpage sur les en-têtes « [date] canal.NIVEAU: »
        $blocs = preg_split(
            '/^\[(\d{4}-\d{2}-\d{2}[^\]]*)\]\s+\S+\.(\w+):/m',
            $contenu,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        $entrees = [];

        for ($i = 1; $i + 2 <= count($blocs); $i += 3) {
            $date = $blocs[$i];
            $niveau = $blocs[$i + 1];
            $texte = trim($blocs[$i + 2]);

            // On ne garde que ce qui concerne l'e-mail
            $pertinent = preg_match(
                '/mail|smtp|sendmail|message-id|subject:|swift|Symfony\\\\Component\\\\Mailer/i',
                $texte
            );

            if (! $pertinent) {
                continue;
            }

            $entrees[] = [
                'date' => $date,
                'niveau' => strtoupper($niveau),
                'texte' => mb_substr($texte, 0, 1500),
            ];
        }

        // Les plus récentes d'abord
        return array_slice(array_reverse($entrees), 0, 15);
    }
}
