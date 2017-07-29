<?php
/**
 * Created by PhpStorm.
 * User: Евгения
 * Date: 05.07.2017
 * Time: 12:02
 */

namespace components;

abstract class Model
{
    public static $instance;
    public $_pdo = null;
    public $table = '';
    private $_select = '*';
    private $_from = '';
    private $_where = null;
    private $_limit = null;
    private $_join = null;
    private $_orderBy = null;
    private $_groupBy = null;
    private $_having = null;
    private $_offset = null;
    private $_grouped = false;
    private $_numRows = 0;
    private $_insertId = null;
    private $_query = null;
    private $_error = null;
    private $_result = "";
    private $_prefix = null;
    public $_debug_mode = false;
    protected $_find_id = false;
    private $_op = [
        '=',
        '!=',
        '<',
        '>',
        '<=',
        '>=',
        '<>'
    ];
    private $_queryCount = 0;
    private $_final_query = null;
    private $_pk = 'id';
    private $_cache = null;

    function __construct($pk = false)
    {
        if ($pk != false) {
            $this->_find_id = $pk;
        }
        $this->_pdo = Db::getConnection();
        $this->table($this->table);
        $this->_result = new \stdClass();
    }

    public static function instance()
    {
        return static::$instance ?: new static();
    }

    function __set($name, $value)
    {
        $this->setValue($name, $value);
    }

    function setValue($name, $value)
    {
        if (method_exists($this, "check" . $name)) {
            if (($time = $this->{"check" . $name}($value)) == false) { //$this->_result->$name =
                $this->_error[$name] = " Ошибка заполнения поля " . $name . " ";
            } else {
                $this->_result->$name = $value;
            }
        } else {
            $this->_result->$name = $value;
        }
    }

    public function noError()
    {
        if ($this->_error == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Удаляет из даты все
     * @param $data
     */
    public function clearData($data)
    {
        foreach ($this->_result as $k => $v) {
            if ($k != $this->_pk) {
                unset($data[$k]);
                $cleared[$k] = $v;
            }
        }
        return $cleared;
    }

    /**
     * Возвращает очищенную от данных этой модели данные
     * @param $data
     */
    public function takeData($data)
    {
        foreach ($this->_result as $k => $v) {
            if (isset($data[$k]) and $k != $this->_pk) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Метод заполняет созданный объект данными из любого массива
     *
     * @param $data
     */
    function fill($data)
    {

        if (!is_object($data)) {
            $data = (object)$data;
        }

        if (!empty($data->{$this->_pk})) {
            $this->setValue($this->_pk, $data->{$this->_pk});
        }
        foreach ($this->_result as $k => $v) {
            if (isset($data->$k)) {
                $this->setValue($k, $data->$k);
            }
        }
        return $this;
    }

    /**
     * Простой метод, позволяющий сохранять данные на основе переданного массива или объекта
     * При этом если в массиве или объекте ЕСТЬ, primary ключ, то идет UPDATE, в противном случае INSERT
     * Есть и третий способ, если заполнен объет, то вызов этого метода, сделает описанные выше действия
     * на созданном объекте
     * Пример:
     * $model->save(array('id'=>1, 'url' = '/ho-ho-ho')); ОБНОВИТ ЗАПИСЬ
     * $model->save(array('url' = '/ho-ho-ho')); Вставит запипис
     * --------------
     * $model->ID=1;
     * $model->URL='/ho-ho-ho';
     * $model->save();
     * Произойдет обновление записи 1.
     *
     * @param bool $data
     *
     * @return bool or primaryKey
     */
    function save($data = false)
    {
        if ($this->_error != null) {
            $this->_print_error();
        } //если есть ошибка не даем сохранить
        if ($data != false) {
            if (is_array($data)) {
                $data = (object)$data;
            }
            $this->_result = $data;
        }
        $pk = $this->_pk;
        if (!empty($this->_result->$pk) and $this->_result->$pk != null) {
            return $this->update($this->_result);
        } else {
            return $this->insert($this->_result);
        }
    }

    function __get($name)
    {
        return isset($this->_result->$name) ? $this->_result->$name : null;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        if (is_array($table)) {
            $f = '';
            foreach ($table as $key) {
                $f .= $this->_prefix . $key . ', ';
            }
            $this->_from = rtrim($f, ', ');
        } else {
            $this->_from = $this->_prefix . $table;
        }
        return $this;
    }

    /**
     * Метод устанавливает поля для выборки. При этом поля могу переданы, как строкй - одно поле, так и массивом
     * $model->select('id');
     * $model->select(array('id','ur'));
     * $model->select('*'); выбрать все
     * Можно и строкой:
     * $model->select(" COUNT(id)");
     *
     * @param $fields
     *
     * @return $this
     */
    public function select($fields)
    {
        $select = (is_array($fields) ? implode(", ", $fields) : $fields);
        $this->_select = ($this->_select == '*' ? $select : $this->_select . ", " . $select);
        return $this;
    }

    public function max($field, $name = null)
    {
        $func = "MAX(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
        $this->_select = ($this->_select == '*' ? $func : $this->_select . ", " . $func);
        return $this;
    }

    public function min($field, $name = null)
    {
        $func = "MIN(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
        $this->_select = ($this->_select == '*' ? $func : $this->_select . ", " . $func);
        return $this;
    }

    public function sum($field, $name = null)
    {
        $func = "SUM(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
        $this->_select = ($this->_select == '*' ? $func : $this->_select . ", " . $func);
        return $this;
    }

    public function count($field, $name = null)
    {
        $func = "COUNT(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
        $this->_select = ($this->_select == '*' ? $func : $this->_select . ", " . $func);
        return $this;
    }

    public function avg($field, $name = null)
    {
        $func = "AVG(" . $field . ")" . (!is_null($name) ? " AS " . $name : "");
        $this->_select = ($this->_select == '*' ? $func : $this->_select . ", " . $func);
        return $this;
    }

    public function join($table, $field1 = null, $op = null, $field2 = null, $type = '')
    {
        $on = $field1;
        $table = $this->_prefix . $table;
        if (!is_null($op)) {
            $on = (!in_array($op, $this->_op) ? $this->_prefix . $field1 . ' = ' . $this->_prefix . $op : $this->_prefix . $field1 . ' ' . $op . ' ' . $this->_prefix . $field2);
        }
        if (is_null($this->_join)) {
            $this->_join = ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        } else {
            $this->_join = $this->_join . ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;
        }
        return $this;
    }

    public function innerJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'INNER ');
        return $this;
    }

    public function leftJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'LEFT ');
        return $this;
    }

    public function rightJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'RIGHT ');
        return $this;
    }

    public function fullOuterJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'FULL OUTER ');
        return $this;
    }

    public function leftOuterJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'LEFT OUTER ');
        return $this;
    }

    public function rightOuterJoin($table, $field1, $op = '', $field2 = '')
    {
        $this->join($table, $field1, $op, $field2, 'RIGHT OUTER ');
        return $this;
    }

    /**
     * Пример использования
     * $model->where('id','>=','5');
     *
     * @param        $where - поле
     * @param null $op - условие > < ....
     * @param null $val - значение
     * @param string $type
     * @param string $and_or - если where передано условие в виде массива, то здесь можно указать AND или OR юзать
     *
     * @return $this
     */
    public function where($where, $op = null, $val = null, $type = '', $and_or = 'AND')
    {
        if (is_array($where)) {
            $_where = [];
            foreach ($where as $column => $data) {
                $_where[] = $type . $column . '=' . $this->escape($data);
            }
            $where = implode(' ' . $and_or . ' ', $_where);
        } else {
            if (is_array($op)) {
                $x = explode('?', $where);
                $w = '';
                foreach ($x as $k => $v) {
                    if (!empty($v)) {
                        $w .= $type . $v . (isset($op[$k]) ? $this->escape($op[$k]) : '');
                    }
                }
                $where = $w;
            } elseif (!in_array($op, $this->_op) || $op == false) {
                $where = $type . $where . ' = ' . $this->escape($op);
            } else {
                $where = $type . $where . ' ' . $op . ' ' . $this->escape($val);
            }
        }
        if ($this->_grouped) {
            $where = '(' . $where;
            $this->_grouped = false;
        }
        if (is_null($this->_where)) {
            $this->_where = $where;
        } else {
            $this->_where = $this->_where . ' ' . $and_or . ' ' . $where;
        }
        return $this;
    }

    public function orWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, '', 'OR');
        return $this;
    }

    public function notWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'AND');
        return $this;
    }

    public function orNotWhere($where, $op = null, $val = null)
    {
        $this->where($where, $op, $val, 'NOT ', 'OR');
        return $this;
    }

    public function grouped(\Closure $obj)
    {
        $this->_grouped = true;
        call_user_func($obj);
        $this->_where .= ')';
        return $this;
    }

    /**
     * Метод принимае либо массив ключей, либо экземпляр модели.
     * @param string $field поле по котомоу будет совпадение
     * @param null $keys - Либо массив ключей, либо объект модели
     * @param string $type - NOT, OR  - приставка к IN
     * @param string $and_or - AND or OR - применятся к условию
     * @return $this
     */
    public function in($field, $keys = null, $type = '', $and_or = 'AND')
    {
        /*
         * Если передан массив
         */
        if (is_array($keys)) {
            $_keys = [];
            foreach ($keys as $k => $v) {
                $_keys[] = (is_numeric($v) ? $v : $this->escape($v));
            }
            $keys = implode(', ', $_keys);
        }
        /*
         * Если передан объект этого класса
         */
        if (is_object($keys) and method_exists($keys, "getQuery")) {
            $keys = $keys->getQuery();
        }
        if (!is_null($keys)) {
            if (is_null($this->_where)) {
                $this->_where = $field . ' ' . $type . 'IN (' . $keys . ')';
            } else {
                $this->_where = $this->_where . ' ' . $and_or . ' ' . $field . ' ' . $type . 'IN (' . $keys . ')';
            }
        }
        return $this;
    }

    public function notIn($field, $keys = null)
    {
        $this->in($field, $keys, 'NOT ', 'AND');
        return $this;
    }

    public function orIn($field, $keys = null)
    {
        $this->in($field, $keys, '', 'OR');
        return $this;
    }

    public function orNotIn($field, $keys = null)
    {
        $this->in($field, $keys, 'NOT ', 'OR');
        return $this;
    }

    public function between($field, $value1, $value2, $type = '', $and_or = 'AND')
    {

        if (is_null($this->_where)) {
            $this->_where = $field . ' ' . $type . 'BETWEEN ' . $this->escape($value1) . ' AND ' . $this->escape($value2);
            // echo $this->_where;
        } else {
            $this->_where = $this->_where . ' ' . $and_or . ' ' . $field . ' ' . $type . 'BETWEEN ' . $this->escape($value1) . ' AND ' . $this->escape($value2);
        }
        return $this;
    }

    public function notBetween($field, $value1, $value2)
    {
        $this->between($field, $value1, $value2, 'NOT ', 'AND');
        return $this;
    }

    public function orBetween($field, $value1, $value2)
    {
        $this->between($field, $value1, $value2, '', 'OR');
        return $this;
    }

    public function orNotBetween($field, $value1, $value2)
    {
        $this->between($field, $value1, $value2, 'NOT ', 'OR');
        return $this;
    }

    public function like($field, $data, $type = '', $and_or = 'AND')
    {
        $like = $this->escape($data);
        if (is_null($this->_where)) {
            $this->_where = $field . ' ' . $type . 'LIKE ' . $like;
        } else {
            $this->_where = $this->_where . ' ' . $and_or . ' ' . $field . ' ' . $type . 'LIKE ' . $like;
        }
        return $this;
    }

    public function orLike($field, $data)
    {
        $this->like($field, $data, '', 'OR');
        return $this;
    }

    public function notLike($field, $data)
    {
        $this->like($field, $data, 'NOT ', 'AND');
        return $this;
    }

    public function orNotLike($field, $data)
    {
        $this->like($field, $data, 'NOT ', 'OR');
        return $this;
    }

    public function limit($limit, $limitEnd = null)
    {
        if (!is_null($limitEnd)) {
            $this->_limit = $limit . ', ' . $limitEnd;
        } else {
            $this->_limit = $limit;
        }
        return $this;
    }

    /**
     * Добавляет сдвиг выборки.
     * @param $num
     * @return $this
     */
    public function offset($num)
    {
        if (is_numeric($num)) {
            $this->_offset = intval($num);
        }
        return $this;
    }

    public function orderBy($orderBy, $order_dir = null, $UPorDown = false)
    {
        if ($orderBy == null) {
            return $this;
        }
        if (!is_null($order_dir)) {
            $this->_orderBy = $orderBy . ' ' . strtoupper($order_dir);
        } else {
            if (stristr($orderBy, ' ') || $orderBy == 'rand()') {
                $this->_orderBy = $orderBy;
            } else {
                $this->_orderBy = $orderBy;
                $this->_orderBy .= ($UPorDown) ? " " . $UPorDown : ' ASC';
            }
        }
        return $this;
    }

    public function groupBy($groupBy)
    {
        if (is_array($groupBy)) {
            $this->_groupBy = implode(', ', $groupBy);
        } else {
            $this->_groupBy = $groupBy;
        }
        return $this;
    }

    public function having($field, $op = null, $val = null)
    {
        if (is_array($op)) {
            $x = explode('?', $field);
            $w = '';
            foreach ($x as $k => $v) {
                if (!empty($v)) {
                    $w .= $v . (isset($op[$k]) ? $this->escape($op[$k]) : '');
                }
            }
            $this->_having = $w;
        } elseif (!in_array($op, $this->_op)) {
            $this->_having = $field . ' > ' . $this->escape($op);
        } else {
            $this->_having = $field . ' ' . $op . ' ' . $this->escape($val);
        }
        return $this;
    }

    public function numRows()
    {
        return $this->_numRows;
    }

    public function insertId()
    {
        return $this->_insertId;
    }

    public function error()
    {
        $msg = '<h1>Database Error</h1>';
        $msg .= '<h4>Query: <em style="font-weight:normal;">"' . $this->_query . '"</em></h4>';
        $msg .= '<h4>Error: <em style="font-weight:normal;">' . $this->_error . '</em></h4>';
        die($msg);
    }

    /**
     * Получает объект записи. Либо false
     * @param bool $pk
     * @param bool $type
     * @return bool|mixed|\PDOStatement|\stdClass|string
     */
    public function getOne($pk = false, $type = false)
    {
        if (!is_array($pk) and !empty($pk)) {
            $this->where($this->_pk, "=", $pk);
        } elseif (!empty($pk)) {
            $this->where($pk);
        }
        return $this->get($type);
    }

    public function get($type = false)
    {
        $this->_limit = 1;
        $query = $this->prepareQuery();
        if ($type == true) {
            return $query;
        } else {
            return $this->query($query, false, (($type == 'array') ? true : false));
        }
    }

    protected function prepareQuery()
    {
        $this->beforeSelect(); //перед селектом
        $query = 'SELECT ' . $this->_select . ' FROM ' . $this->_from;
        if (!is_null($this->_join)) {
            $query .= $this->_join;
        }
        if (!is_null($this->_where)) {
            $query .= ' WHERE ' . $this->_where;
        }
        if (!is_null($this->_groupBy)) {
            $query .= ' GROUP BY ' . $this->_groupBy;
        }
        if (!is_null($this->_having)) {
            $query .= ' HAVING ' . $this->_having;
        }
        if (!is_null($this->_orderBy)) {
            $query .= ' ORDER BY ' . $this->_orderBy;
        }
        if (!is_null($this->_limit)) {
            $query .= ' LIMIT ' . $this->_limit;
        }
        if (!is_null($this->_offset)) {
            $query .= ' OFFSET  ' . $this->_offset;
        }
        return $this->_final_query = $query;
    }

    public function getAll($type = false)
    {
        if ($type == true) {
            return $this->prepareQuery();
        } else {
            return $this->query($this->prepareQuery(), true, (($type == 'array') ? true : false));
        }
    }

    public function insert($data = false)
    {
        $this->beforeInsert();
        if ($data == false and !empty($this->_result)) {
            $data = (array)$this->_result;
        }
        if (empty($data) and empty($this->_result)) {
            throw new \Exception("ОШИБКА ЗАПРОСА INSERT НЕТ ДАННЫХ");
        }
        if (!empty($data)) {
            $this->reset();
        } //если передали массив сбрасываем все.

        if (is_object($data)) {
            $data = (array)$data;
        }
        $columns = array_keys($data);
        $column = '`' . implode('`,`', $columns) . "`";
        $val = implode(
            ', ', array_map(
                    [
                        $this,
                        'escape'
                    ], $data
                )
        );
        $query = 'INSERT INTO ' . $this->_from . ' (' . $column . ') VALUES (' . $val . ')';
        if ($this->_debug_mode == false) {
            $query = $this->query($query);
            if ($query) {
                $this->_insertId = $this->_pdo->lastInsertId();
                return $this->insertId();
            } else {
                return false;
            }
        } else {
            echo $query;
        }
    }

    public function update($data = false)
    {
        $this->beforeUpdate();
        if (empty($data) and empty($this->_result)) {
            throw new \Exception("ОШИБКА ЗАПРОСА UPDATE НЕТ ДАННЫХ");
        }
        if (empty($data) and !empty($this->_result)) {
            $data = (array)$this->_result;
        }

        if (!empty($data)) {
            $this->reset();
        } //если передали массив сбрасываем все.
        if (empty($this->_from)) {
            $this->_from = $this->table;
        }
        $query = 'UPDATE ' . $this->_from . ' SET ';
        $values = [];

        if (is_array($data) or is_object($data)) {
            foreach ($data as $column => $val) {
                if ($column == $this->_pk) {
                    $this->where($this->_pk, "=", $val);
                }
                if ($column != $this->_pk) {
                    if (empty($val)) {
                        $val = '';
                    }
                    $values[] = "`" . $column . '`=' . $this->escape($val);
                }
            }
            $query .= implode(',', $values);
        } else {
            $query .= $data;
        }

        if (!is_null($this->_where)) {
            $query .= ' WHERE ' . $this->_where;
        }
        if (!is_null($this->_orderBy)) {
            $query .= ' ORDER BY ' . $this->_orderBy;
        }
        if (!is_null($this->_limit)) {
            $query .= ' LIMIT ' . $this->_limit;
        }
        if (!is_null($this->_offset)) {
            $query .= ' OFFSET ' . $this->_offset;
        }

        if ($this->_debug_mode == false) {
            return $this->query($query);
        } else {
            echo $query;
        }
    }

    public function delete($id = false)
    {

        $this->beforeDelete();
        if (is_array($id)) {
            $this->where($id);
        } elseif ($id != false) {
            $this->where($this->_pk, "=", $id);
        } else {
            $ptime = $this->_pk;
            if (intval($this->$ptime) >= 1) {
                $this->where($this->_pk, "=", $this->$ptime);
            }
        }
        $query = 'DELETE FROM ' . $this->_from;
        if (!is_null($this->_where)) {
            $query .= ' WHERE ' . $this->_where;
        }
        if (!is_null($this->_orderBy)) {
            $query .= ' ORDER BY ' . $this->_orderBy;
        }
        if (!is_null($this->_limit)) {
            $query .= ' LIMIT ' . $this->_limit;
        }
        if ($query == 'DELETE FROM ' . $this->_from) {
            $query = 'TRUNCATE TABLE ' . $this->_from;
        }
        //TODO - сделать для truncate отдельный метод, это жесть - часто все убивает, ОПАСНО!
        //die($query);
        if ($this->_debug_mode == false) {
            return $this->query($query);
        } else {
            echo $query;
        }
    }

    public function query($query, $all = true, $array = false)
    {
        $type = "";
//        if ($this->_error != null) {
//            $this->_print_error();
//        } //если есть ошибка не даем работать с БД
        $this->reset();
        if (is_array($all)) {
            $x = explode('?', $query);
            $q = '';
            foreach ($x as $k => $v) {
                if (!empty($v)) {
                    $q .= $v . (isset($all[$k]) ? $this->escape($all[$k]) : '');
                }
            }
            $query = $q;
        }
        $this->_query = preg_replace('/\s\s+|\t\t+/', ' ', trim($query));
        $type = false;

        if (substr(trim($this->_query), 0, 3) == "SEL") {
            $type = "S";
        }
        if (substr(trim($this->_query), 0, 3) == "UPD") {
            $type = "U";
        }
        if (substr(trim($this->_query), 0, 3) == "INS") {
            $type = "I";
        }
        if (substr(trim($this->_query), 0, 3) == "DEL") {
            $type = "D";
        }
        if ($this->_debug_mode == true) {
            echo $query;
            return false;
        }

        $cache = false;
        if (!is_null($this->_cache)) {
            $cache = $this->_cache->getCache($this->_query, $array);
        }
        if (!$cache && $type == "S") {
            try {
                $sql = $this->_pdo->query($this->_query);
            } catch (\PDOException $e) {

                $error = new Errors();
                $error->e500(false, $e);


            }

            if ($sql) {
                $this->_numRows = $sql->rowCount();
                if (($this->_numRows > 0)) {
                    if ($all) {
                        $q = [];
                        while ($result = ($array == false) ? $sql->fetchAll(\PDO::FETCH_OBJ) : $sql->fetchAll(\PDO::FETCH_ASSOC)) {
                            $q[] = $result;
                        }
                        $this->_result = $q[0];
                    } else {
                        $q = ($array == false) ? $sql->fetch(\PDO::FETCH_OBJ) : $sql->fetch(\PDO::FETCH_ASSOC);
                        $this->_result = $q;
                    }
                } else {
                    //если затронуло менее 1 строки, знать вернем false
                    return false;
                }
                if (!is_null($this->_cache)) {
                    $this->_cache->setCache($this->_query, $this->_result);
                }
                $this->_cache = null;
            } else {
                $this->_cache = null;
                $this->_error = $this->_pdo->errorInfo();
                $this->_error = $this->_error[2];
                return $this->_query . $this->error();
            }
        } elseif ($type != "S") {
            $this->_cache = null;
            try {
                $this->_result = $this->_pdo->query($this->_query);
            } catch (\PDOException $e) {
                echo 'Выброшено исключение: ', $e->getMessage(), "\n";
            }
            if (!$this->_result) {
                $this->_error = $this->_pdo->errorInfo();
                $this->_error = $this->_error[2];
                return $this->_query . $this->error();
            } else {

            }
        } else {
            $this->_cache = null;
            $this->_result = $cache;
        }
        $this->_queryCount++;
        if ($type == "U") {
            $this->afterUpdate();
        }
        if ($type == "S") {
            $this->afterSelect();
        }
        if ($type == "I") {
            $this->afterInsert();
        }
        if ($type == "D") {
            $this->afterDelete();
        }
        return $this->_result;
    }


    public function escape($data)
    {
        if (is_null($data)) {
            return null;
        }
        return $this->_pdo->quote(trim($data));
    }

    public function cache($time)
    {
        $this->_cache = new Cache($this->_cacheDir, $time);
        return $this;
    }

    public function queryCount()
    {
        return $this->_queryCount;
    }

    public function getQuery()
    {
        $this->_query = $this->prepareQuery();
        return $this->_query;
    }

    private function reset()
    {
        // $this->_select = '*';
        // $this->_from = null; //в модели это нуна!
        // $this->_where = null;
        $this->_limit = null;
        $this->_offset = null;
        $this->_orderBy = null;
        $this->_groupBy = null;
        $this->_having = null;
        //  $this->_join = null;
        $this->_grouped = false;
        $this->_numRows = 0;
        $this->_insertId = null;
        $this->_query = null;
        $this->_error = null;
        //$this->_result   = [];
        return;
    }

    public function clear()
    {
        $this->_select = '*';
        $this->_from = null; //в модели это нуна!
        $this->_where = null;
        $this->_limit = null;
        $this->_offset = null;
        $this->_orderBy = null;
        $this->_groupBy = null;
        $this->_having = null;
        $this->_join = null;
        $this->_grouped = false;
        $this->_numRows = 0;
        $this->_insertId = null;
        $this->_query = null;
        $this->_error = null;
        $this->_result = new \stdClass();
        $this->table($this->table);
        return $this;
    }

    public function from($param)
    {
        $this->table($param);
        return $this;
    }

    public function beforeSelect()
    {
        return true;
    }

    public function afterSelect()
    {
        return true;
    }

    public function beforeInsert()
    {
        return true;
    }

    public function afterInsert()
    {
        return true;
    }

    public function beforeUpdate()
    {
        return true;
    }

    public function afterUpdate()
    {
        return true;
    }

    public function beforeDelete()
    {
        return true;
    }

    public function afterDelete()
    {
        return true;
    }

    function __destruct()
    {
        $this->_pdo = null;
    }

    function _print_error()
    {
        echo "<pre>";
        print_r($this->_error);
        die();
    }
}

