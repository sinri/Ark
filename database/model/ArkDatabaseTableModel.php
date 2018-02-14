<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 15:10
 */

namespace sinri\ark\database\model;


use sinri\ark\core\ArkHelper;
use sinri\ark\database\ArkPDO;

/**
 * Class ArkDatabaseTableModel
 * @package sinri\ark\database\model
 */
abstract class ArkDatabaseTableModel
{
    protected $scheme;
    protected $table;

    public function __construct()
    {
        $this->scheme = $this->mappingSchemeName();
        $this->table = $this->mappingTableName();
    }

    /**
     * @return null|string
     */
    protected function mappingSchemeName()
    {
        return null;
    }

    /**
     * @return string
     */
    abstract protected function mappingTableName();

    /**
     * @return string
     */
    protected function getTableExpressForSQL()
    {
        $e = ($this->scheme === null ? "" : '`' . $this->scheme . "`.");
        $e .= "`{$this->table}`";
        return $e;
    }

    /**
     * @return ArkPDO
     */
    abstract public function db();

    /**
     * @return false|string
     */
    public static function now()
    {
        return date('Y-m-d H:i:s');
    }

    protected final function buildCondition($conditions, $glue = 'AND')
    {
        $condition_sql = "";
        if (is_string($conditions)) {
            $condition_sql = $conditions;
        } elseif (is_array($conditions)) {
            $c = [];
            foreach ($conditions as $key => $value) {
                if (is_a($value, ArkSQLCondition::class)) {
                    try {
                        $c[] = $value->makeConditionSQL();
                    } catch (\Exception $e) {
                        // ignore the error
                    }
                } else {
                    if (is_array($value)) {
                        $x = [];
                        foreach ($value as $value_piece) {
                            $x[] = $this->db()->quote($value_piece);
                        }
                        $x = implode(",", $x);
                        $c[] = " `{$key}` in (" . $x . ") ";
                    } else {
                        $c[] = " `{$key}`=" . $this->db()->quote($value) . " ";
                    }
                }
            }
            $condition_sql = implode($glue, $c);
        }
        return trim($condition_sql);
    }

    /**
     * @param array|string $conditions
     * @return array|bool
     */
    public function selectRow($conditions)
    {
        $condition_sql = $this->buildCondition($conditions, 'AND');
        if ($condition_sql === '') {
            $condition_sql = "1";
        }

        $table = $this->getTableExpressForSQL();
        $sql = "SELECT * FROM {$table} WHERE {$condition_sql} LIMIT 1";
        try {
            return $this->db()->getRow($sql);
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * @param array|string $conditions
     * @param int $limit
     * @param int $offset
     * @return array|bool
     */
    public function selectRows($conditions, $limit = 0, $offset = 0)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "SELECT * FROM {$table} WHERE {$condition_sql} ";
        $limit = intval($limit, 10);
        $offset = intval($offset, 10);
        if ($limit > 0) {
            $sql .= " limit {$limit} ";
            if ($offset > 0) {
                $sql .= " offset {$offset} ";
            }
        }
        try {
            return $this->db()->getAll($sql);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array|string $conditions
     * @return int|bool
     */
    public function selectRowsForCount($conditions)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "SELECT count(*) FROM {$table} WHERE {$condition_sql} ";

        try {
            $count = $this->db()->getOne($sql);
            return intval($count, 10);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $conditions
     * @param null|string $sort "field","field desc"," f1 asc, f2 desc"
     * @param int $limit
     * @param int $offset
     * @param null|string $refKey normally PK or UK if you want to get map rather than list
     * @return array|bool
     */
    public function selectRowsWithSort($conditions, $sort = null, $limit = 0, $offset = 0, $refKey = null)
    {
        $condition_sql = $this->buildCondition($conditions);
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "SELECT * FROM {$table} WHERE {$condition_sql} ";

        if ($sort) {
            $sql .= "order by " . $sort;
        }

        $limit = intval($limit, 10);
        $offset = intval($offset, 10);
        if ($limit > 0) {
            $sql .= " limit {$limit} ";
            if ($offset > 0) {
                $sql .= " offset {$offset} ";
            }
        }
        try {
            $all = $this->db()->getAll($sql);
            if ($refKey) {
                $all = ArkHelper::turnListToMapping($all, $refKey);
            }
            return $all;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $data
     * @param null $pk
     * @return bool|string
     */
    public function insert($data, $pk = null)
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = $this->db()->quote($value);
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $table = $this->getTableExpressForSQL();
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
        try {
            return $this->db()->insert($sql, $pk);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function replace($data)
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = $this->db()->quote($value);
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $table = $this->getTableExpressForSQL();
        $sql = "replace INTO {$table} ({$fields}) VALUES ({$values})";
        try {
            return $this->db()->insert($sql);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $conditions
     * @param $data
     * @return int
     */
    public function update($conditions, $data)
    {
        $condition_sql = $this->buildCondition($conditions, "AND");
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $data_sql = $this->buildCondition($data, ",");
        $table = $this->getTableExpressForSQL();
        $sql = "UPDATE {$table} SET {$data_sql} WHERE {$condition_sql}";
        try {
            return $this->db()->exec($sql);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $conditions
     * @return int
     */
    public function delete($conditions)
    {
        $condition_sql = $this->buildCondition($conditions, "AND");
        if ($condition_sql === '') {
            $condition_sql = "1";
        }
        $table = $this->getTableExpressForSQL();
        $sql = "DELETE FROM {$table} WHERE {$condition_sql}";
        try {
            return $this->db()->exec($sql);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $dataList
     * @param null $pk
     * @return bool|string
     */
    public function batchInsert($dataList, $pk = null)
    {
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            foreach ($dataList as $data) {
                $tmp = [];
                if (count($data) != count($fields)) {
                    return false;
                }
                foreach ($data as $key => $value) {
                    $tmp[] = $this->db()->quote($value);
                }
                $values[] = "(" . implode(",", $tmp) . ")";
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpressForSQL();
            $sql = "INSERT INTO {$table} ({$fields}) VALUES {$values}";
            return $this->db()->insert($sql, $pk);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param array $dataList
     * @return bool|string
     */
    public function batchReplace($dataList)
    {
        try {
            $fields = [];
            $values = [];

            foreach ($dataList[0] as $key => $value) {
                $fields[] = "`{$key}`";
            }
            foreach ($dataList as $data) {
                $tmp = [];
                if (count($data) != count($fields)) {
                    return false;
                }
                foreach ($data as $key => $value) {
                    $tmp[] = $this->db()->quote($value);
                }
                $values[] = "(" . implode(",", $tmp) . ")";
            }
            $fields = implode(",", $fields);
            $values = implode(",", $values);
            $table = $this->getTableExpressForSQL();
            $sql = "REPLACE INTO {$table} ({$fields}) VALUES {$values}";
            return $this->db()->exec($sql);
        } catch (\Exception $exception) {
            return false;
        }
    }


    /**
     * @return ArkDatabaseTableFieldDefinition[]
     * @throws \Exception
     */
    protected function loadTableDesc()
    {
        $fieldDefinition = [];
        $field_list = $this->db()->getAll("desc " . $this->getTableExpressForSQL());
        if (empty($field_list)) {
            throw new \Exception("Seems no such table " . $this->getTableExpressForSQL());
        }
        foreach ($field_list as $field) {
            $fieldDefinition[$field['Field']] = ArkDatabaseTableFieldDefinition::makeInstanceWithDescResultRow($field);
        }
        return $fieldDefinition;
    }

    /**
     * When you design a model for a certain table which is eventually designed,
     * you might run this method to get `@property` lines for the model class PHPDoc.
     * @throws \Exception
     */
    public function devShowFieldsForPHPDoc()
    {
        echo "THIS IS A HELPER FOR DEV." . PHP_EOL;
        $fieldDefinition = $this->loadTableDesc();
        foreach ($fieldDefinition as $definition) {
            echo " * @property " . $definition->getTypeCategory() . ' ' . $definition->getName() . PHP_EOL;
        }
    }

    protected $fields;

    /**
     * 如果model类里定义了字段名作为property，此方法可以加载一行的关联数组的数据以复写
     * @param array $row result of `selectRow`
     */
    public function loadFieldsFromRowArray($row)
    {
        $this->fields = [];
        foreach ($row as $key => $value) {
            $this->fields[$key] = $value;
        }
    }

    public function __get($name)
    {
        return ArkHelper::readTarget($this->fields, $name, '');
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->fields, $name, $value);
    }
}