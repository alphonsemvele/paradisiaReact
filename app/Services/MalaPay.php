<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client de l'API Malapay v1.
 *
 * En attendant l'intégration des opérateurs mobile money par pays, les
 * paiements Paradisia passent par les portefeuilles Malapay : on vérifie la
 * devise et le solde, puis on débite. Si le portefeuille est vide ou dans une
 * autre devise, Malapay renvoie les représentants à contacter pour le créditer.
 */
class MalaPay
{
    /**
     * Clé versionnée : incrémenter le suffixe invalide immédiatement les
     * anciennes entrées, sans avoir à vider le cache sur le serveur.
     */
    private const CLE_CACHE_PAYS = 'malapay.pays.v2';

    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $cle = null,
    ) {}

    public function estConfigure(): bool
    {
        return filled($this->url()) && filled($this->clef());
    }

    /**
     * Pays disponibles et leur devise. Mis en cache : la liste ne change
     * pratiquement jamais et le formulaire de paiement s'ouvre instantanément.
     *
     * @return array<int, array{code:string,nom:string,drapeau:?string,devise:string,indicatif:?string}>
     */
    public function pays(): array
    {
        if (! $this->estConfigure()) {
            return [];
        }

        $enCache = Cache::get(self::CLE_CACHE_PAYS);

        if (is_array($enCache) && $enCache !== []) {
            return $enCache;
        }

        $resultat = $this->appeler('get', '/v1/pays', []);
        $pays = $resultat['ok'] ? ($resultat['data'] ?? []) : [];

        // Un résultat vide n'est jamais mis en cache : une panne passagère ou
        // une configuration incomplète gèlerait sinon une liste vide 24 h,
        // et le paiement resterait inutilisable même une fois le problème réglé.
        if ($pays !== []) {
            Cache::put(self::CLE_CACHE_PAYS, $pays, now()->addDay());
        }

        return $pays;
    }

    /**
     * Vérifie un portefeuille avant paiement : existence, devise, solde.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string, representants?:array}
     */
    public function verifierPortefeuille(string $code, string $devise, float $montant, ?string $pays = null): array
    {
        return $this->appeler('post', '/v1/portefeuilles/verifier', [
            'code' => $code,
            'devise' => $devise,
            'montant' => $montant,
            'pays' => $pays,
        ]);
    }

    /**
     * Débite le portefeuille. La référence rend l'appel idempotent : en cas de
     * double soumission ou de reprise après incident, le client n'est pas
     * débité deux fois.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string, representants?:array}
     */
    public function debiter(
        string $code,
        float $montant,
        string $devise,
        string $reference,
        ?string $description = null,
        ?string $service = null,
    ): array {
        return $this->appeler('post', '/v1/portefeuilles/debiter', [
            'code' => $code,
            'montant' => $montant,
            'devise' => $devise,
            'reference' => $reference,
            'description' => $description,
            // Détermine le barème de frais appliqué par Malapay
            'service' => $service,
        ]);
    }

    /**
     * État d'un paiement en attente de validation par son titulaire.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string}
     */
    public function statutDebit(string $reference): array
    {
        return $this->appeler('get', '/v1/portefeuilles/debits/'.urlencode($reference), []);
    }

    /** Représentants habilités à créditer un portefeuille. */
    public function representants(?string $pays = null): array
    {
        $resultat = $this->appeler('get', '/v1/representants', array_filter(['pays' => $pays]));

        return $resultat['ok'] ? ($resultat['data'] ?? []) : [];
    }

    /**
     * Opérateurs mobile money disponibles pour un pays (vide = portefeuille seul).
     *
     * @return array<int, array{code:string, nom:string}>
     */
    public function operateurs(string $pays): array
    {
        $resultat = $this->appeler('get', '/v1/operateurs', ['pays' => $pays]);

        return $resultat['ok'] ? ($resultat['data']['operateurs'] ?? []) : [];
    }

    /**
     * Initie un paiement mobile money (Orange/MTN). Renvoie notamment l'URL de
     * paiement (`url_paiement`) vers laquelle rediriger le client.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string}
     */
    public function payerMobile(
        string $reference,
        float $montant,
        string $devise,
        string $pays,
        string $operateur,
        string $telephone,
        ?string $description = null,
        ?string $service = null,
        ?string $urlRetour = null,
    ): array {
        return $this->appeler('post', '/v1/paiements/mobile', [
            'reference' => $reference,
            'montant' => $montant,
            'devise' => $devise,
            'pays' => $pays,
            'operateur' => $operateur,
            'telephone' => $telephone,
            'description' => $description,
            'service' => $service,
            'url_retour' => $urlRetour,
        ]);
    }

    /**
     * État d'un paiement mobile money.
     *
     * @return array{ok:bool, data?:array, code?:string, message?:string}
     */
    public function statutMobile(string $reference): array
    {
        return $this->appeler('get', '/v1/paiements/mobile/'.urlencode($reference), []);
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    /**
     * Enveloppe unique des appels : une panne réseau ou une erreur Malapay
     * ressort toujours sous la même forme, jamais en exception non gérée.
     */
    private function appeler(string $methode, string $chemin, array $donnees): array
    {
        if (! $this->estConfigure()) {
            return [
                'ok' => false,
                'code' => 'MALAPAY_NOT_CONFIGURED',
                'message' => 'Le paiement Malapay n\'est pas encore configuré sur ce site.',
            ];
        }

        try {
            $reponse = $this->requete()->{$methode}($this->url().$chemin, $donnees);
        } catch (ConnectionException $e) {
            Log::warning('Malapay injoignable : '.$e->getMessage());

            return [
                'ok' => false,
                'code' => 'MALAPAY_UNREACHABLE',
                'message' => 'Le service de paiement est momentanément injoignable. Réessayez dans un instant.',
            ];
        }

        $corps = $reponse->json() ?? [];

        if ($reponse->successful() && ($corps['success'] ?? false)) {
            return ['ok' => true, 'data' => $corps['data'] ?? []];
        }

        // Quota dépassé. Malapay limite les routes de paiement à 20 appels par
        // minute ; le corps de la réponse est celui du limiteur, pas le nôtre,
        // d'où un message formulé ici.
        if ($reponse->status() === 429) {
            $secondes = (int) $reponse->header('Retry-After');

            Log::warning('Malapay : quota d\'appels dépassé', ['chemin' => $chemin, 'retry_after' => $secondes]);

            return [
                'ok' => false,
                'code' => 'MALAPAY_RATE_LIMITED',
                'message' => $secondes > 0
                    ? "Trop de tentatives de paiement. Patientez {$secondes} seconde".($secondes > 1 ? 's' : '').' avant de réessayer.'
                    : 'Trop de tentatives de paiement. Patientez une minute avant de réessayer.',
                'retry_after' => $secondes ?: 60,
            ];
        }

        $erreur = $corps['error'] ?? [];
        $code = $erreur['code'] ?? 'MALAPAY_ERROR';

        // Le projet Paradisia a été suspendu, archivé ou rejeté côté Malapay :
        // la clé est valide mais n'encaisse plus. Cas administratif, pas une
        // erreur de l'investisseur — il ne sert à rien de lui dire de réessayer.
        if ($code === 'PROJECT_INACTIVE') {
            Log::error('Malapay : le projet Paradisia n\'est plus actif, encaissement suspendu.');

            return [
                'ok' => false,
                'code' => $code,
                'message' => 'Les paiements sont momentanément suspendus. Nos équipes sont prévenues, réessayez plus tard.',
            ];
        }

        return [
            'ok' => false,
            'code' => $code,
            'message' => $erreur['message'] ?? 'Le paiement a échoué. Réessayez ou contactez un représentant.',
            'representants' => $erreur['representants'] ?? [],
        ];
    }

    private function requete()
    {
        return Http::withToken($this->clef())
            ->acceptJson()
            ->timeout(config('services.malapay.timeout', 15))
            ->retry(2, 200, when: $this->reprisePertinente(...), throw: false);
    }

    /**
     * Sans callback, Laravel rejoue TOUTE réponse non-2xx. Un « solde
     * insuffisant » (422) partait donc en deux appels identiques, et un 429 en
     * deux également — de quoi épuiser deux fois plus vite le quota que Malapay
     * applique désormais (20 appels/minute sur les routes de paiement).
     *
     * On ne rejoue que ce qui a une chance d'aboutir au second essai : une
     * coupure réseau ou une panne passagère côté Malapay. Un refus métier, une
     * clé invalide ou un dépassement de quota sont définitifs à cette échelle.
     */
    private function reprisePertinente(\Throwable $e): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }

        return $e instanceof RequestException && $e->response->serverError();
    }

    private function url(): ?string
    {
        return rtrim($this->baseUrl ?? (string) config('services.malapay.url'), '/') ?: null;
    }

    private function clef(): ?string
    {
        return $this->cle ?? config('services.malapay.key');
    }
}
