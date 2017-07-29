<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 14.07.2017
 * Time: 12:06
 */
/**
 * @var array $data
 */
//echo"<pre>";print_r($data);echo"</pre>";
?>
<div class="block-header">
    <ul class="breadcrumb">
        <li>
            <a href="/">Главная</a>
        </li>
        <li>
            <a href="/users">Пользователи</a>
        </li>
    </ul>
</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>Пользователи</h2>
            </div>
            <? if (!empty($data['users'])) { ?>

                <div class="body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="table-log">
                            <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Логин</th>
                                <th>ФИО</th>
                                <th>Последняя активность</th>
                                <th>Права</th>
                                <th>Блокировка</th>
                            </tr>
                            </thead>
                            <tbody>
                            <? foreach ($data['users'] AS $user) { ?>
                                <tr class="person-row">
                                    <td>
                                        <?= $user->id; ?></td>

                                    <td>
                                        <?= $user->login; ?>
                                    </td>
                                    <td>
                                        <?= $user->fio; ?>
                                    </td>
                                    <td>
                                        <?= date('d.m.Y H:i:s', strtotime($user->login_at)); ?> </td>
                                    <td><?=\modules\user\models\User::$rights[$user->rights];?></td>
                                    <td><span class="<?=\modules\user\models\User::$classCss[$user->blocked];?>"><?=\modules\user\models\User::$blocked[$user->blocked];?></td>
                                </tr>
                            <? } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            <? } ?>

        </div>
    </div>
</div>
