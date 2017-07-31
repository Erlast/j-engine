<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 31.07.2017
 * Time: 10:16
 */

namespace components;


class Console extends App
{
    private $internalRoute;
    private $parameters;
    public static $module;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->getURI();
    }
    public static function stdin($raw = false)
    {
        return $raw ? fgets(\STDIN) : rtrim(fgets(\STDIN), PHP_EOL);
    }

    public static function stdout($string)
    {
        return fwrite(\STDOUT, $string);
    }

    public static function confirm($message, $default = false)
    {
        while (true) {
            static::stdout($message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:');
            $input = trim(static::stdin());

            if (empty($input)) {
                return $default;
            }

            if (!strcasecmp($input, 'y') || !strcasecmp($input, 'yes')) {
                return true;
            }

            if (!strcasecmp($input, 'n') || !strcasecmp($input, 'no')) {
                return false;
            }
        }
    }

    public function run()
    {
//        todo придумать правадоступа

//        if(self::$url=='registration'){
//
//        }elseif(!\modules\user\models\User::isAuthorized()){
//            self::$url='login';
//        }

        // $internalRoute = self::$url;
        // die($internalRoute);
//        foreach ($this->routes as $uriPattern => $path) {
//            $result = NULL;
//            if (preg_match("~$uriPattern~", $this->internalRoute)) {
//                $result        = 1;
//                $internalRoute = preg_replace("~$uriPattern~", $path, $this->internalRoute);
//            }
//            if ($result !== NULL) {
//                break;
//            }
//        }
//
//        if ($this->internalRoute == "") {
//            $this->internalRoute = App::instance()->defaultRote . "/" . App::instance()->defaultAction;
//        }
        //echo"<pre>";print_r(App::instance());echo"</pre>";
        $segments       = explode('/', $this->internalRoute);
        $controllerName = array_shift($segments);
        App::$module    = "";
        if (!empty(Core::$app->modules)) {
            if (key_exists($controllerName, Core::$app->modules)) {
                App::$module   = $controllerName;
                $controllerName = array_shift($segments);
            }
        }

        if (empty($segments)) {
            $segments[] =  Core::$app->defaultAction;
        }
        $controllerName = ucfirst($controllerName . 'Controller');
        $actionName     = 'action' . ucfirst(array_shift($segments));


        if (class_exists($controllerClass = '\\controllers\\' . $controllerName)) {
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $this->parameters);
        } elseif (class_exists($controllerClass = '\\modules\\' . App::$module . '\\controllers\\' . $controllerName)) {
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $this->parameters);
        } elseif (class_exists($controllerClass = '\\components\\' . $controllerName)) {
            //echo"<pre>";print_r($actionName);echo"</pre>";;
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $this->parameters);
        }

    }

    private function getURI()
    {
        if (!empty($_SERVER['argv'])) {

            // foreach ($_SERVER['argv'] as $param) {
            //f (strpos($param, $option) !== false) {
            //  $path = substr($param, strlen($option));
            // if (!empty($path) && is_file($file = Yii::getAlias($path))) {
            $this->internalRoute = trim($_SERVER['argv'][1], '/');
            $this->parameters    = array(trim($_SERVER['argv'][2]));
//                    } else {
//                        exit("The configuration file does not exist: $path\n");
//                    }
//                }
            // }
        }
    }
}