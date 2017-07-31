<?php
/**
 * Created by PhpStorm.
 * User: Женя
 * Date: 25.05.2017
 * Time: 18:38
 */

namespace components;

class Router
{
    private $routes;
    public static $url;

    public function __construct()
    {
        $this->routes = Core::$app->routes;
        self::$url    = $this->getURI();
    }


    public function run()
    {
//        todo придумать правадоступа

//        if(self::$url=='registration'){
//
//        }elseif(!\modules\user\models\User::isAuthorized()){
//            self::$url='login';
//        }

        $internalRoute = self::$url;
       // die($internalRoute);
        foreach ($this->routes as $uriPattern => $path) {
            $result = NULL;
            if (preg_match("~$uriPattern~", self::$url)) {
                $result        = 1;
                $internalRoute = preg_replace("~$uriPattern~", $path, self::$url);
            }
            if ($result !== NULL) {
                break;
            }
        }

        if ($internalRoute == "") {
            $internalRoute = Core::$app->defaultRote . "/" . Core::$app->defaultAction;
        }
        $segments       = explode('/', $internalRoute);
        $controllerName = array_shift($segments);
        App::$module    = "";
        if (key_exists($controllerName,Core::$app->modules)) {
            App::$module    = $controllerName;
            $controllerName = array_shift($segments);
        }
        if (empty($segments)) {
            $segments[] = Core::$app->defaultAction;
        }
        $controllerName = ucfirst($controllerName . 'Controller');
        $actionName     = 'action' . ucfirst(array_shift($segments));

        $parameters = $segments;


        if (class_exists($controllerClass = '\\controllers\\' . $controllerName)) {
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $parameters);
        } elseif (class_exists($controllerClass = '\\modules\\' . App::$module . '\\controllers\\' . $controllerName)) {
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $parameters);
        } elseif (class_exists($controllerClass = '\\components\\' . $controllerName)) {
            $controllerObject = new $controllerClass();
            call_user_func_array(array($controllerObject, $actionName), $parameters);
        }

    }

    private function getURI()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            return trim($_SERVER['REQUEST_URI'], '/');
        }

    }

}