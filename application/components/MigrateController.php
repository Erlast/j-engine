<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 29.07.2017
 * Time: 15:32
 */


namespace components;

use Exception;

/**
 * @property-read Migration $migration
 * Class MigrateController
 * @package components
 */
class MigrateController extends Controller
{
    private $migration;

    public function __construct()
    {
        $this->setMigration(new Migration());

    }

    public function actionCreate($name = null)
    {
        $this->migration->create($name);
        exit;
    }

    public function actionIndex($limit = 0)
    {
        $this->migration->checkEnvironment();
        $migrations = $this->migration->getNewMigrations();

        if (empty($migrations)) {
            Console::stdout("No new migrations found. Your system is up-to-date.\n");

            return true;
        }

        $total = count($migrations);
        $limit = (int)$limit;
        if ($limit > 0) {
            $migrations = array_slice($migrations, 0, $limit);
        }
        $n = count($migrations);
        if ($n === $total) {
            Console::stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n");
        } else {
            Console::stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n");
        }

        foreach ($migrations as $migration) {
            Console::stdout("\t" . $migration . "\n");
        }
        Console::stdout("\n");

        $applied = 0;
        if (Console::confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migration->migrateUp($migration)) {
                    Console::stdout("\n$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were') . " applied.\n");
                    Console::stdout("\nMigration failed. The rest of the migrations are canceled.\n");

                    return true;
                }
                $applied++;
            }

            Console::stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " applied.\n");
            Console::stdout("\nMigrated up successfully.\n");
        }
    }

    public function actionDown($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int)$limit;
            if ($limit < 1) {
                throw new Exception('The step argument must be greater than 0.');
            }
        }

        $migrations = $this->migration->getMigrationHistory($limit);

        if (empty($migrations)) {
            Console::stdout("No migration has been done before.\n");

            return true;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        Console::stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n");
        foreach ($migrations as $migration) {
            Console::stdout("\t$migration\n");
        }
        Console::stdout("\n");

        $reverted = 0;
        if (Console::confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migration->migrateDown($migration)) {
                    Console::stdout("\n$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were') . " reverted.\n");
                    Console::stdout("\nMigration failed. The rest of the migrations are canceled.\n");

                    return true;
                }
                $reverted++;
            }
            Console::stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " reverted.\n");
            Console::stdout("\nMigrated down successfully.\n");
        }
    }

    public function actionHelp()
    {
        $this->migration->help();
    }


    /**
     * @param mixed $migration
     */
    private function setMigration(Migration $migration)
    {
        $this->migration = $migration;
    }

}