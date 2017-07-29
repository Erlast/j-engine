<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 13.07.2017
 * Time: 19:35
 */

namespace modules\user\models;

/**
 * @property string id - айди
 * @property string login - логин
 * @property string password - пароль
 * @property string login_at - залогинен в
 * @property integer blocked - заблокирован/не заблокирован
 * @property string fio - фио
 * @property integer rights - права
 * Class User
 * @package modules\user\models
 */
class User extends \components\Model
{

    const BLOCKED_NO          = 1;
    const BLOCKED_YES         = 2;
    const MIN_LENGTH_PASSWORD = 6;
    const ROLE_ADMIN          = 1;
    const ROLE_USER           = 2;
    const ROLE_GUEST          = 3;

    public $table = "user";
    public $errors = array();
    public static $instance;

    public static $blocked = [
        self::BLOCKED_NO  => 'Активен',
        self::BLOCKED_YES => 'Заблокирован',
    ];

    public static $classCss = [
        self::BLOCKED_NO  => 'badge bg-green',
        self::BLOCKED_YES => 'badge bg-red'
    ];

    public static $rights = [
        self::ROLE_USER  => 'Пользователь',
        self::ROLE_ADMIN => 'Администратор',
        self::ROLE_GUEST => 'Гость'
    ];

    public function __construct()
    {
        parent::__construct(false);
        $this->setId('');
        $this->setLogin('');
        $this->setFio('');
        $this->setLoginAt('');
        $this->setBlocked('');
        $this->setRights('');

    }

    public static function initFirst()
    {
        if (empty($_SESSION['user'])) {
            $u                = new self();
            $_SESSION['user'] = json_encode($u->initUser());
            return true;
        }
        return false;
    }


    public static function instance()
    {
        if (static::$instance) {
            return static::$instance;
        } else {
            return new static();
        }
    }

    public static function factory($id = false)
    {
        return self::instance()->build($id);
    }

    private function build($id)
    {
        if ($id == false || $id == 'null' || !$this->getOne(['id' => $id])) {
            return static::instance();
        }
        return $this;
    }

    public function initUser()
    {
        $obj         = new \stdClass();
        $obj->id     = $this->id;
        $obj->rights = User::ROLE_GUEST;
        $obj->bloked = User::BLOCKED_NO;
        return $obj;
    }


    public static function current()
    {

        if ($_SESSION['user'] !== null) {
            $result = self::instance(false)->fill(json_decode($_SESSION['user']));
        } else {
            $result           = self::instance();
            $_SESSION['user'] = json_encode(self::initUser());
        }
        return $result;

    }

    public static function getLogin()
    {
        $user = self::current();
        return $user->login;
    }


    /**
     * checks to what level user belongs
     * @param $level
     * @return mixed
     */
    public function isHasRight($right)
    {
        $userCurrent = self::current();
        if ($userCurrent->rights == $right) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * checks if user is admin
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->isHasRight(self::ROLE_ADMIN);
    }

    /**
     * @alias for stop()
     * @return mixed
     */
    public function logout()
    {
        return $this->stop();
    }

    /**
     * remove user data from $_SESSION , fill $_SESSION default data
     * @return mixed
     */
    public function stop()
    {
        unset ($_SESSION['user']);
        self::initFirst();
        return true;
    }

    /**
     * authorizes user with $email and $pass, fill $_SESSION with user data from BD
     * @param $email
     * @param $pass
     * @return mixed
     */
    public function login($login, $pass)
    {
        if ($this->getOne(['login' => $login, 'password' => $pass])) {
            $data['id']       = $this->id;
            $data['login_at'] = date('Y-m-d H:i:s');
            $this->setLoginAt($data['login_at']);
            $_SESSION['user'] = $this->toSession();
            $this->update($data);
            return true;


        } else {
            return false;
        }

    }

    public static function isAuthorized()
    {
        $user = self::current();

        if (!empty($user->id) || (intval($user->id) > 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @param $fio
     */
    public function setFio($fio)
    {
        $this->fio = $fio;
    }

    /**
     * @param $rights
     */
    public function setRights($rights)
    {
        $this->rights = $rights;
    }

    /**
     * @param $blocked
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;
    }

    /**
     * @param mixed $login_at
     */
    public function setLoginAt($login_at)
    {
        $this->login_at = $login_at;
    }

    public function generateHashWithSalt($password)
    {
        $salt = substr(sha1($password), 10, 20) . "\3\1\2\6";
        return sha1(sha1($password) . $salt);
    }


    public function toSession()
    {
        $obj           = new \stdClass();
        $obj->id       = $this->id;
        $obj->login    = $this->login;
        $obj->fio      = $this->fio;
        $obj->rights   = $this->rights;
        $obj->login_at = $this->login_at;
        $obj->blocked  = $this->blocked;
        return json_encode($obj);
    }


}