<?php

namespace components;

use PDO;

/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 25.05.2017
 * Time: 22:01
 */
class Db
{
    public static function getConnection()
    {
        $params = App::instance()->db;
        $dsn    = $params['dsn'] . ";charset=" . $params['charset'];
        $db     = new PDO($dsn, $params['user'], $params['password']);
        return $db;
    }
}