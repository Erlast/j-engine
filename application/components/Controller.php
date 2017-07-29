<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 05.07.2017
 * Time: 12:25
 */

namespace components;


abstract class Controller
{

    public function render($file, $data = [], $layout = "")
    {
        $content = ($this->renderContent($file, $data));
        $render_layout  = $this->findLayoutFile($layout);
        return $this->findFile($render_layout, ['content' => $content]);
    }

    private function renderContent($file, $data = [])
    {
        if (App::$module) {
            $path = App::instance()->basePath . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . App::$module . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . $file . ".php";
        } else {
            $path = App::instance()->basePath . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . $file . ".php";
        }

        return $this->findFile($path, $data);
    }

    private function findLayoutFile($layout = "")
    {
        if ($layout) {
            App::instance()->layout = $layout;
        }
        if (!isset(App::instance()->layout)) {
            return false;
        }
        $file = App::instance()->basePath . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . App::instance()->layout;

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.php';
        return $path;
    }

    function renderTemplate()
    {

        $file = App::instance()->basePath . "/views/layouts/" . App::instance()->layout . ".php";
        if ($this->isFile($file)) {
            $this->render($file);
        } else {
            die($file);
        }
        return $this;
    }

    function renderPartial($file, $data = [])
    {
        return $this->renderContent($file, $data);
    }

    private function findFile($file, $data)
    {
        ob_start();
        if (!$this->includeFile($file, $data)) {
            throw new \Exception("Не удалось подлючить темлейт: " . $file);
        }
        ob_implicit_flush(false);
        $render = ob_get_contents();
        ob_end_clean();
        return $render;
    }

    private function includeFile($file, $data)
    {
        if (file_exists($file)) {
            if (!include($file)) {
                throw new \Exception('Не удалось подключить файл');
            }
        } else {
            throw new \Exception('Файл не существует');
        }
        return true;

    }

    private function isFile($file)
    {
        if (file_exists($file) and is_readable($file)) {
            return true;
        } else {
            return false;
        }
    }
}