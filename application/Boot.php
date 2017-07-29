<?php

set_include_path(get_include_path()
                . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR . 'controllers'
);
set_include_path(get_include_path()
               . PATH_SEPARATOR . __DIR__  . DIRECTORY_SEPARATOR . "src". DIRECTORY_SEPARATOR . "fpdf"
);

spl_autoload_register(function ($class_name) {
    $file = stream_resolve_include_path(str_replace("\\", DIRECTORY_SEPARATOR, $class_name) . ".php");
    file_exists($file) && require_once $file;
});
session_start();
