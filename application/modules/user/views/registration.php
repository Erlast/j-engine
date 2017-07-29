<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 14.07.2017
 * Time: 10:55
 */
/**
 * @var array $data
 */
?>
<form id="sign_up" method="POST">
    <div class="msg">Регистрация нового пользователя</div>
    <?=(!empty($_SESSION['registration']['errors']))?"<div class='alert alert-danger'>".$_SESSION['registration']['errors']."</div>":""; unset($_SESSION['registration']);?>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
        <div class="form-line">
            <input type="text" class="form-control" name="fio" placeholder="ФИО" value="<?= $data['user']->fio; ?>"
                   required autofocus>
        </div>
    </div>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
        <div class="form-line">
            <input type="text" class="form-control" name="login" value="<?= $data['user']->login; ?>"
                   placeholder="Логин" required autofocus>
        </div>
    </div>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
        <div class="form-line">
            <input type="password" class="form-control" name="password"
                   minlength="<?= \modules\user\models\User::MIN_LENGTH_PASSWORD; ?>" placeholder="Пароль" required>
        </div>
    </div>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
        <div class="form-line">
            <input type="password" class="form-control" name="confirm"
                   minlength="<?= \modules\user\models\User::MIN_LENGTH_PASSWORD; ?>" placeholder="Повтор пароля"
                   required>
        </div>
    </div>

    <button class="btn btn-block btn-lg bg-pink waves-effect" type="submit">Зарегистрироваться</button>

    <div class="m-t-25 m-b--5 align-center">
        <a href="/login">Уже зарегистрированы?</a>
    </div>
</form>
