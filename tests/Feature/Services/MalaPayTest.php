<?php

namespace Tests\Feature\Services;

use App\Services\MalaPay;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Comportement du client face aux réponses de Malapay.
 *
 * Malapay limite désormais ses routes de paiement à 20 appels par minute. Le
 * client rejouait toute réponse non-2xx : un refus métier ou un dépassement de
 * quota partait en deux appels identiques, ce qui épuisait le quota deux fois
 * plus vite sans aucune chance d'aboutir au second essai.
 */
class MalaPayTest extends TestCase
{
    private function client(): MalaPay
    {
        return new MalaPay('https://malapay.test/api', 'mpk_test_'.str_repeat('a', 32));
    }

    #[Test]
    public function un_refus_metier_n_est_pas_rejoue(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_FUNDS', 'message' => 'Solde insuffisant.'],
            ], 422),
        ]);

        $resultat = $this->client()->debiter('CODE123456', 5000, 'XAF', 'REF-1');

        $this->assertFalse($resultat['ok']);
        $this->assertSame('INSUFFICIENT_FUNDS', $resultat['code']);
        Http::assertSentCount(1);
    }

    #[Test]
    public function une_cle_invalide_n_est_pas_rejouee(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Clé API introuvable.'],
            ], 401),
        ]);

        $this->client()->verifierPortefeuille('CODE123456', 'XAF', 5000);

        Http::assertSentCount(1);
    }

    #[Test]
    public function un_depassement_de_quota_n_est_pas_rejoue_et_indique_le_delai(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Too Many Attempts.'], 429, ['Retry-After' => '37']),
        ]);

        $resultat = $this->client()->debiter('CODE123456', 5000, 'XAF', 'REF-2');

        $this->assertFalse($resultat['ok']);
        $this->assertSame('MALAPAY_RATE_LIMITED', $resultat['code']);
        $this->assertSame(37, $resultat['retry_after']);
        $this->assertStringContainsString('37 secondes', $resultat['message']);
        Http::assertSentCount(1);
    }

    #[Test]
    public function une_panne_serveur_est_rejouee(): void
    {
        Http::fake(['*' => Http::response('', 503)]);

        $this->client()->verifierPortefeuille('CODE123456', 'XAF', 5000);

        // Une 5xx peut être passagère. `retry(2)` vaut deux tentatives au
        // total, pas une plus deux.
        Http::assertSentCount(2);
    }

    #[Test]
    public function un_projet_suspendu_donne_un_message_sans_invitation_a_reessayer(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => false,
                'error' => ['code' => 'PROJECT_INACTIVE', 'message' => "Le projet associé à cette clé n'est pas actif."],
            ], 403),
        ]);

        $resultat = $this->client()->debiter('CODE123456', 5000, 'XAF', 'REF-3');

        $this->assertSame('PROJECT_INACTIVE', $resultat['code']);
        $this->assertStringContainsString('momentanément suspendus', $resultat['message']);
        Http::assertSentCount(1);
    }

    #[Test]
    public function un_paiement_abouti_ressort_sous_forme_exploitable(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => ['reference' => 'REF-4', 'statut' => 'en_attente', 'validation_requise' => true],
            ], 202),
        ]);

        $resultat = $this->client()->debiter('CODE123456', 5000, 'XAF', 'REF-4');

        $this->assertTrue($resultat['ok']);
        $this->assertSame('en_attente', $resultat['data']['statut']);
    }
}
