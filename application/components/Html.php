<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 04.07.2017
 * Time: 12:14
 */

namespace components;


class Html
{

    public $content;
    public $title;

    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }



}