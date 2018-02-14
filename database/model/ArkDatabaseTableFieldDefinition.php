<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 15:27
 */

namespace sinri\ark\database\model;


use sinri\ark\core\ArkHelper;

class ArkDatabaseTableFieldDefinition
{
    protected $name;
    protected $type;
    protected $typeCategory;
    protected $nullable;

    protected function __construct()
    {
    }

    /**
     * @param $row
     * @return ArkDatabaseTableFieldDefinition
     */
    public static function makeInstanceWithDescResultRow($row)
    {
        $field = new ArkDatabaseTableFieldDefinition();
        $field->name = ArkHelper::readTarget($row, 'Field');
        $field->nullable = ArkHelper::readTarget($row, 'Null', 'NO') === 'YES';

        $field->type = ArkHelper::readTarget($row, 'Type', '');
        if (preg_match('/^[A-Za-z0-9]+/', $field->type, $matches)) {
            $field->typeCategory = self::determineTypeCategory($matches[0]);
        } else {
            $field->typeCategory = $field->type;
        }

        return $field;
    }

    protected static function determineTypeCategory($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'bit':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
                return "integer";
            case 'bigint'://for bigint it sometimes sucks for PHP when number too large
                return "integer";
            case 'SERIAL'://SERIAL is an alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE.
                return "string";
            case 'decimal':
            case 'dec':
            case 'double':
            case 'real':
                return 'double';
            case 'float':
                return 'float';
            case 'bool':
            case 'boolean':
                // actually tinyint(1)
                return "int";
            case 'data':
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'year':
                return 'string';
            case 'char':
            case 'varchar':
            case 'binary':
            case 'varbinary':
            case 'tinyblob':
            case 'tinytext':
            case 'blob':
            case 'text':
            case 'mediumblob':
            case 'mediumtext':
            case 'longblob':
            case 'longtext':
            case 'enum':
            case 'set':
                return 'string';
            default:
                return "string";
        }
    }

    /**
     * @return mixed
     */
    public function getTypeCategory()
    {
        return $this->typeCategory;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getNullable()
    {
        return $this->nullable;
    }

    /**
     * @param mixed $nullable
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
    }
}