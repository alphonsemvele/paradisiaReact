<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La base de production a été créée par import SQL : sa table migrations est
 * vide alors que les tables existent déjà. Un migrate ordinaire tenterait de
 * tout recréer et échouerait. Cette commande marque comme exécutées les
 * migrations dont le travail est déjà en place, pour que migrate ne joue que
 * les vraies nouveautés. Idempotente, ne modifie aucune table métier.
 */
class BaselineMigrations extends Command
{
    protected $signature = 'migrate:baseline';

    protected $description = 'Marque comme exécutées les migrations dont les tables existent déjà (base importée par SQL)';

    /**
     * Migrations de modification (sans Schema::create) : considérées comme
     * déjà exécutées si l'état qu'elles produisent est constaté en base.
     *
     * @var array<string, callable(): bool>
     */
    private array $modificationChecks;

    /**
     * La base importée utilise des noms hérités de l'ancien système : la
     * table qu'une migration voudrait créer y existe sous un autre nom.
     * On considère alors la migration comme déjà appliquée plutôt que de
     * créer une table jumelle vide.
     *
     * @var array<string, string> nom migration => nom hérité en base
     */
    private const LEGACY_TABLE_NAMES = [
        'ask_questions' => 'askquestions',
        'category_projects' => 'categories_projects',
        'connecteds' => 'connected',
        'deconnecteds' => 'deconnected',
        'media' => 'medias',
        'pack_pubs' => 'pack_pub',
        'users_pack_pub_seens' => 'users_pack_pub_seen',
        'password_reset_tokens' => 'password_resets',
    ];

    public function handle(): int
    {
        $this->modificationChecks = [
            // Le rename a déjà eu lieu si "inscriptions" existe.
            '2026_06_27_125331_rename_formations_to_inscriptions' => fn () => Schema::hasTable('inscriptions'),
            '2026_06_27_125333_add_formation_id_to_inscriptions_table' => fn () => Schema::hasTable('inscriptions') && Schema::hasColumn('inscriptions', 'formation_id'),
            '2026_06_27_130315_add_mode_to_formations_table' => fn () => Schema::hasTable('formations') && Schema::hasColumn('formations', 'mode'),
        ];

        if (! Schema::hasTable('migrations')) {
            DB::statement('CREATE TABLE migrations (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL, batch INT NOT NULL)');
            $this->info('Table migrations créée.');
        }

        $done = DB::table('migrations')->pluck('migration')->all();
        $batch = (int) DB::table('migrations')->max('batch') + 1;
        $inserted = 0;

        foreach (glob(database_path('migrations/*.php')) as $file) {
            $name = basename($file, '.php');

            if (in_array($name, $done, true)) {
                continue;
            }

            if ($this->alreadyApplied($name, $file)) {
                DB::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
                $this->line("baseline : $name");
                $inserted++;
            }
        }

        $this->info($inserted > 0 ? "$inserted migration(s) marquée(s) comme exécutée(s)." : 'Rien à baseliner.');

        return self::SUCCESS;
    }

    private function alreadyApplied(string $name, string $file): bool
    {
        if (isset($this->modificationChecks[$name])) {
            return ($this->modificationChecks[$name])();
        }

        // Migration de création : déjà appliquée si l'une de ses tables
        // existe, sous son nom propre ou sous son nom hérité. ("l'une" et non
        // "toutes" : certaines tables annexes des migrations d'origine n'ont
        // jamais existé dans la base importée.)
        preg_match_all("/Schema::create\('([^']+)'/", file_get_contents($file), $m);

        if ($m[1] === []) {
            return false; // autre migration de modification : migrate décidera
        }

        return collect($m[1])->contains(
            fn ($table) => Schema::hasTable($table)
                || Schema::hasTable(self::LEGACY_TABLE_NAMES[$table] ?? $table)
        );
    }
}
