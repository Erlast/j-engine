<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 13.07.2017
 * Time: 19:50
 */

namespace modules\user\controllers;


use components\Controller;
use modules\user\models\User;
use components\Html;

/**
 * @property-read User $user
 * Class IndexController
 * @package modules\user\controllers
 */
class IndexController extends Controller
{

    private $user;

    public function init()
    {
        $this->setUser(new User());
    }

    public function actionLogin()
    {
        $this->setUser(new User());
        if (!empty($_POST)) {
            if ($this->user->login($_POST['login'], $this->user->generateHashWithSalt($_POST['password']))) {
                header('Location: /');
                exit;
            }
        }
        Html::instance()->title = 'Авторизация';
        echo $this->render('login', [], 'login');
        exit;
    }

    public function actionLogout()
    {
        $this->setUser(new User());
        if ($this->user->logout()) {
            header('Location: /');
            exit;
        }
    }

    public function actionRegistration()
    {
        $this->setUser(new User());
        $user = User::factory();
        if (!empty($_POST)) {

            $user->password = $_POST['password'];
            $user->login    = $_POST['login'];
            $user->fio      = $_POST['fio'];
            $user->blocked  = User::BLOCKED_NO;

            if ($_POST['password'] != $_POST['confirm']) {
                $_SESSION['registration']['errors'] = 'Пароли не совпали';
                Html::instance()->title = 'Регистрация';
                echo $this->render('registration', ['user' => $user], 'login');
                exit;
            }
            $user->password = $this->user->generateHashWithSalt($_POST['password']);
            $user->rights   = User::ROLE_USER;
            if ($user->save()) {
                Html::instance()->title = 'Авторизация';
                echo $this->render('login', [], 'login');
                exit;
            };
            exit;
        }
        Html::instance()->title = 'Регистрация';
        echo $this->render('registration', ['user' => $user], 'login');
        exit;
    }

    public function actionIndex()
    {
        $this->setUser(new User());
        Html::instance()->title = 'Пользователи';
        echo $this->render('index', ['users' => $this->user->getAll()]);
        exit;
    }


    /**
     * @param User $user
     */
    private function setUser(User $user)
    {
        $this->user = $user;
    }
}