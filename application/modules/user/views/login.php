<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 13.07.2017
 * Time: 19:54
 */
?>
<form id="sign_in" method="POST" action="/user/login">
    <div class="msg">Введите логин/пароль</div>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
        <div class="form-line">
            <input type="text" class="form-control" name="login" placeholder="Логин" required autofocus>
        </div>
    </div>
    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
        <div class="form-line">
            <input type="password" class="form-control" name="password" placeholder="Пароль" required>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-8 p-t-5">
            <input type="checkbox" name="rememberme" id="rememberme" class="filled-in chk-col-pink">
            <label for="rememberme">Запомнить меня</label>
        </div>
        <div class="col-xs-4">
            <button class="btn btn-block bg-pink waves-effect" type="submit">Войти</button>
        </div>
    </div>
    <div class="row m-t-15 m-b--20">
        <div class="col-xs-6">
            <a href="/registration">Регистрация</a>
        </div>
        <div class="col-xs-6 align-right">
            <a href="forgot-password.html">Забыли пароль?</a>
        </div>
    </div>
</form>
