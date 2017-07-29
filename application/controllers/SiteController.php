<?php

namespace controllers;

use components\Controller;
use components\Html;

/**
 * Created by PhpStorm.
 * User: Женя
 * Date: 25.05.2017
 * Time: 17:28
 */
class SiteController extends Controller
{
    public function actionIndex()
    {
        Html::instance()->title = "Главная";
        echo $this->render('site/index');
        return true;

    }
}