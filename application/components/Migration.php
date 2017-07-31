<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 29.07.2017
 * Time: 15:03
 */

namespace components;

/**
 * Class Migration
 * @package components
 */
class Migration extends Model
{
    protected $db;
    public $table = "migrations";
    public $migrationPath = 'migrations';
    const BASE_MIGRATION_NAME = "m00000_00000_base";

    public function migrateUp($class)
    {
        if ($class === self::BASE_MIGRATION_NAME) {
            return true;
        }

        Console::stdout("*** applying " . $class->version . "\n");
        $start     = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->up() !== false) {
            $this->addMigrationHistory($class);
            $time = microtime(true) - $start;
            Console::stdout("*** applied $class (time: " . sprintf('%.3f', $time) . "s)\n\n");

            return true;
        } else {
            $time = microtime(true) - $start;
            Console::stdout("*** failed to apply $class (time: " . sprintf('%.3f', $time) . "s)\n\n");

            return false;
        }

    }

    public function migrateDown($class)
    {
        if ($class === self::BASE_MIGRATION_NAME) {
            return true;
        }

        // echo $class;
        Console::stdout("*** reverting $class\n");
        $start     = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->down() !== false) {
            $this->removeMigrationHistory($class);
            $time = microtime(true) - $start;
            Console::stdout("*** reverted $class (time: " . sprintf('%.3f', $time) . "s)\n\n");

            return true;
        } else {
            $time = microtime(true) - $start;
            Console::stdout("*** failed to revert $class (time: " . sprintf('%.3f', $time) . "s)\n\n");

            return false;
        }
    }

    public function down()
    {

    }

    public function getMigrationHistory($limit)
    {

        $rows = $this->getAll();

        $history = [];
        foreach ($rows as $key => $row) {
            if ($row->version === self::BASE_MIGRATION_NAME) {
                continue;
            }
            if (preg_match('/m?(\d{6}_?\d{6})(\D.*)?$/is', $row->version, $matches)) {
                $time                  = str_replace('_', '', $matches[1]);
                $row->canonicalVersion = $time;
            } else {
                $row->canonicalVersion = $row->version;
            }
            $row->apply_time = (int)$row->apply_time;
            $history[]       = $row;
        }

        usort($history, function ($a, $b) {
            if ($a->apply_time === $b->apply_time) {
                if (($compareResult = strcasecmp($b->canonicalVersion, $a->canonicalVersion)) !== 0) {
                    return $compareResult;
                }
                return strcasecmp($b->version, $a->version);
            }
            return ($a->apply_time > $b->apply_time) ? -1 : +1;
        });

        $history     = array_slice($history, 0, $limit);
        $new_history = [];
        foreach ($history AS $item) {
            $new_history[$item->version] = $item->apply_time;
        }
        $history = $new_history;

        return $history;
    }

    public function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getAll() as $item) {
            $applied[$item->version] = true;
        };
        $migrations = [];

        $handle = opendir(Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                $class = $matches[1];
                if (!empty($namespace)) {
                    $class = $namespace . '\\' . $class;
                }
                $time = str_replace('_', '', $matches[2]);
                if (!isset($applied[$class])) {
                    $migrations[$time . '\\' . $class] = $class;
                }
            }
        }
        closedir($handle);
        ksort($migrations);

        return array_values($migrations);
    }

    protected function addMigrationHistory($version)
    {
        $this->insert([
                          'version'    => $version,
                          'apply_time' => time(),
                      ]);
    }

    protected function createMigration($class)
    {
        $class = trim($class, '\\');
        if (strpos($class, '\\') === false) {
            $file = Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
            require_once($file);
        }

        return new $class();
    }

    protected function removeMigrationHistory($version)
    {
        $this->clear();
        $this->delete([
                          'version' => $version,
                      ]);
    }

    public function create($name)
    {
        $version = gmdate('ymd_His') . "_" . $name;
        Console::stdout('Create migration ' . $version . PHP_EOL);
        if (!file_exists(Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath)) {
            mkdir(Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath, 0777);
        }
        $file    = $this->createFullFileName($version);
        $class   = $this->createClassName($version);
        $content =
            <<<END
                <?php
                use components\Migration;
                  
                class {$class} extends Migration
                {
                    public function up() {
                        return true;
                    }
                 
                    public function down() {
                        return true;
                    }
                }
END;
        file_put_contents($file, $content);
    }

    public function help()
    {
        echo <<<END
Usage:
    php je migrate/<action>
Actions:
    up [<count>]
    down [<count>]
    create [<name>]
 
END;
    }

    private function createClassName($version)
    {
        return 'm' . $version;
    }

    private function createFullFileName($version)
    {
        return Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath . DIRECTORY_SEPARATOR . $this->createFileName($version);
    }

    private function createFileName($version)
    {
        return 'm' . $version . '.php';
    }

    public function checkEnvironment()
    {
        if (!file_exists(Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath)) {
            mkdir(Core::$app->basePath . DIRECTORY_SEPARATOR . $this->migrationPath);
        }
        if (!$this->query("Show tables like '" . $this->table . "'")->execute()) {
            Console::stdout("Creating migration history table " . $this->table . "...");
            if ($this->query('CREATE TABLE IF NOT EXISTS ' . $this->table . ' (version varchar(180) NOT NULL PRIMARY KEY,
            apply_time integer) ENGINE=MyISAM DEFAULT CHARSET=utf8;')->execute()
            ) {
                $this->insert(['version' => self::BASE_MIGRATION_NAME, 'apply_time' => time()]);
            };

            Console::stdout("Done.\n");
        }


    }
}