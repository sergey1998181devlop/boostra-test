<?php

namespace Messengers\Core\Models;

use JsonException;
use Messengers\Core\DataBase\PDOConnect;
use PDO;
use PDOStatement;

/**
 *
 */
abstract class Model
{

    public $id;

    /**
     * Дата создания записи в базе
     * @var string
     */
    public $date_create;

    /**
     * Дата обновления записи в базеж
     * @var string
     */
    public $date_update;

    /**
     * uid записи в базе
     * @var string
     */
    public $uid;

    /**
     * Сохранить запись в базе
     * @return $this|null
     * @throws JsonException
     */
    final public function save(): ?self
    {
        $this->uid = $this->getUid();
        $model = $this->getModel();
        if (!$this->id && !$model) {
            return $this->insert();
        }
        if (!$model) {
            $model = new static();
        }
        foreach ($this as $field => $value) {
            if ($value) {
                $model->$field = $value;
            }
        }
        return $model->update();
    }

    /**
     * Сгенерировать uid записи или вернуть имеющийся
     * @return string
     * @throws JsonException
     */
    private function getUid(): string
    {
        if (!$this->uid) {
            return preg_replace(
                '~^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})~u',
                '$1-$2-$3-$4-$5',
                md5(json_encode($this, JSON_THROW_ON_ERROR))
            );
        }
        return $this->uid;
    }

    /**
     * Получить запись из базы по условию
     * @param mixed $values
     * @param string $field
     * @param string $condition
     * @return $this|null
     */
    final public function find($values, string $field = 'id', string $condition = '='): ?self
    {
        if ($values) {
            $sql = "SELECT * FROM " . static::$tableName;
            $stmt = $this->exec($sql, $values, $field, $condition);
            if ($stmt && $res = $stmt->fetchObject(static::class)) {
                return $res;
            }
        }
        return null;
    }

    /**
     * Добавить запись в базу
     * @return mixed
     */
    private function insert(): ?self
    {
        $fields = '';
        $values = '';
        $binds = [];
        $this->date_update = date('Y-m-d H:i:s');
        $this->date_create = date('Y-m-d H:i:s');
        foreach ($this as $filed => $value) {
            $bind = ':param_' . $filed;
            $binds[$bind] = $value;
            $fields .= $filed . ', ';
            $values .= $bind . ', ';
        }
        $sql = sprintf(
            "INSERT INTO " . static::$tableName . " (%s) VALUES (%s)",
            trim($fields, ', '),
            trim($values, ', ')
        );
        $pdo = PDOConnect::instance();
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($binds)) {
            return $this->find($pdo->lastInsertId());
        }
        return null;
    }

    /**
     * Обновить запись в базе
     * @return $this|null
     */
    private function update(): ?self
    {
        $set = '';
        $binds = [];
        $this->date_update = date('Y-m-d H:i:s');
        foreach ($this as $filed => $value) {
            if ($filed !== 'date_create' && $filed !== 'uid') {
                $bind = ':param_' . $filed;
                $set .= $filed . ' = ' . $bind . ', ';
                $binds[$bind] = $value;
            }
        }
        $sql = sprintf(
            "UPDATE " . static::$tableName . " SET %s WHERE id = :param_id",
            trim($set, ', ')
        );
        $pdo = PDOConnect::instance();
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($binds)) {
            return $this->find($this->id);
        }
        return null;
    }

    /**
     * Получить все записи из базы по условию
     * @param array|object|string|int|null $values
     * @param string $field
     * @param string $condition
     * @return array
     */
    final public function all($values = null, string $field = 'id', string $condition = '='): array
    {
        $sql = "SELECT * FROM " . static::$tableName;
        $stmt = $this->exec($sql, $values, $field, $condition);
        if ($stmt && $res = $stmt->fetchAll(PDO::FETCH_CLASS, static::class)) {
            return $res;
        }
        return [];
    }

    /**
     * Инициализация таблиц в базе
     * @return bool
     */
    abstract public function init(): bool;

    /**
     * @param string $sql
     * @param array|object|string|int|null $values
     * @param string $field
     * @param string $condition
     * @return PDOStatement|null
     */
    private function exec(string $sql, $values = null, string $field = 'id', string $condition = '='): ?PDOStatement
    {
        $binds = [];
        if (is_string($values) || is_int($values)) {
            $sql .= " WHERE " . $field . " " . $condition . " :param_" . $field;
            $binds[':param_' . $field] = $values;
        }
        if (is_array($values) || is_object($values)) {
            $str = '';
            foreach ($values as $fieldName => $value) {
                $bind = ':param_' . $fieldName;
                $str .= ' ' . $fieldName . ' = ' . $bind . ' AND';
                $binds[$bind] = $value;
            }
            $sql .= " WHERE " . trim($str, ' AND');
        }
        $stmt = PDOConnect::instance()->prepare($sql);
        if ($stmt && $stmt->execute($binds)) {
            return $stmt;
        }
        return null;
    }

    private function getModel(): ?self
    {
        $model = null;
        if (isset(static::$uniqFields)) {
            $binds = [];
            foreach (static::$uniqFields as $field) {
                $binds[$field] = $this->$field;
            }
            $model = $this->find($binds);
        }
        if (!$model) {
            $model = $this->find(['uid' => $this->uid]);
        }
        return $model;
    }

}