<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 03.07.2017
 * Time: 18:40
 */

namespace components;

class App
{

    public $routes;
    public $db;
    public $basePath;
    public $layout;
    public $modulesPath;
    public $modules;
    public static $module;
    public $defaultRote = 'site';
    public $defaultAction = 'index';
    protected static $instance;

    public static function instance($config = [])
    {

        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function __construct($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        return $this;
    }

    public function run()
    {
       // \modules\user\models\User::initFirst();
        $router = new Router();
        $router->run();
    }
}