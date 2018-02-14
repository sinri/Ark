<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 15:11
 */

namespace sinri\ark\database\model;


use sinri\ark\database\ArkPDO;

class ArkSQLCondition
{
    const OP_EQ = "=";
    const OP_GT = ">";
    const OP_EGT = ">=";
    const OP_LT = "<";
    const OP_ELT = "<=";
    const OP_NEQ = "<>";
    const OP_NULL_SAFE_EQUAL = "<=>";
    const OP_IS = "IS";
    const OP_IS_NOT = "IS NOT";
    const OP_IN = "IN";
    const OP_NOT_IN = "NOT IN";
    const OP_LIKE = "LIKE";
    const OP_NOT_LIKE = "NOT LIKE";
    const OP_BETWEEN = "BETWEEN";
    const OP_NOT_BETWEEN = "NOT BETWEEN";
    //const OP_GREATEST="GREATEST";
    //const OP_LEAST="LEAST";

    const CONST_TRUE = "TRUE";
    const CONST_FALSE = "FALSE";
    const CONST_NULL = "NULL";

    const LIKE_LEFT_WILDCARD = "LIKE_LEFT_WILDCARD";
    const LIKE_RIGHT_WILDCARD = "LIKE_RIGHT_WILDCARD";
    const LIKE_BOTH_WILDCARD = "LIKE_BOTH_WILDCARD";

    protected $operate;
    protected $field;
    protected $value;

    public function __construct($field, $operate, $value, $addition = null)
    {
        $this->field = $field;
        $this->operate = $operate;
        $this->value = $value;

        if ($this->operate === self::OP_LIKE || $this->operate === self::OP_NOT_LIKE) {
            $this->value = ArkPDO::dryQuote($this->value);
            switch ($addition) {
                case self::LIKE_LEFT_WILDCARD:
                    $this->value = "concat('%'," . $this->value . ")";
                    break;
                case self::LIKE_RIGHT_WILDCARD:
                    $this->value = "concat(" . $this->value . ",'%')";
                    break;
                case self::LIKE_BOTH_WILDCARD:
                    $this->value = "concat('%'," . $this->value . ",'%')";
                    break;
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function makeConditionSQL()
    {
        switch ($this->operate) {
            case self::OP_EQ:
            case self::OP_GT:
            case self::OP_EGT:
            case self::OP_LT:
            case self::OP_ELT:
            case self::OP_NEQ:
            case self::OP_NULL_SAFE_EQUAL:
                return "`{$this->field}` " . $this->operate . " " . ArkPDO::dryQuote($this->value);
                break;
            case self::OP_IS:
            case self::OP_IS_NOT:
                if (!in_array($this->value, [self::CONST_FALSE, self::CONST_TRUE, self::CONST_NULL])) {
                    throw new \Exception("ERROR, YOU MUST USE CONSTANT FOR IS COMPARISION!");
                }
                return "`{$this->field}` " . $this->operate . " " . $this->value;
                break;
            case self::OP_IN:
            case self::OP_NOT_IN:
                if (!is_array($this->value) || empty($this->value)) {
                    throw new \Exception("ERROR, YOU MUST GIVE AN ARRAY OF STRING FOR IN OPERATION!");
                }
                $group = [];
                foreach ($group as $item) {
                    $group[] = ArkPDO::dryQuote($item);
                }
                return "`{$this->field}` " . $this->operate . " (" . implode(",", $group) . ")";
                break;
            case self::OP_LIKE:
            case self::OP_NOT_LIKE:
                // NOTE: value is preprocessed in constructor
                return "`{$this->field}` " . $this->operate . " " . ($this->value);
                break;
            case self::OP_BETWEEN:
            case self::OP_NOT_BETWEEN:
                return "`{$this->field}` " . $this->operate . " " . ArkPDO::dryQuote($this->value[0]) . " AND " . ArkPDO::dryQuote($this->value[1]);
                break;
            default:
                throw new \Exception("ERROR, UNKNOWN OPERATE");
        }
    }

}