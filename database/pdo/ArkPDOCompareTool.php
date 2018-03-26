<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/3/13
 * Time: 10:23
 */

namespace sinri\ark\database\pdo;


use sinri\ark\core\ArkHelper;

class ArkPDOCompareTool
{
    const INSTANCE_CODE_A = "A";
    const INSTANCE_CODE_B = "B";

    protected $dbs = [];

    protected $shouldCheckTableCreateDDL = false;
    protected $shouldCheckTableFieldsDefinition = true;
    protected $shouldCheckTableRowCount = true;
    protected $bigTablesToAvoidRowCount = [];

    /**
     * ArkPDOCompareTool constructor.
     * @param ArkPDOConfig $configA
     * @param ArkPDOConfig $configB
     * @throws \Exception
     */
    public function __construct(ArkPDOConfig $configA, ArkPDOConfig $configB)
    {
        $this->dbs[self::INSTANCE_CODE_A] = new ArkPDO($configA);
        $this->dbs[self::INSTANCE_CODE_A]->connect();
        $this->dbs[self::INSTANCE_CODE_B] = new ArkPDO($configB);
        $this->dbs[self::INSTANCE_CODE_B]->connect();

        $this->shouldCheckTableCreateDDL = false;
        $this->shouldCheckTableFieldsDefinition = true;
        $this->shouldCheckTableRowCount = true;
        $this->bigTablesToAvoidRowCount = [];
    }

    /**
     * @return bool
     */
    public function isShouldCheckTableCreateDDL(): bool
    {
        return $this->shouldCheckTableCreateDDL;
    }

    /**
     * @param bool $shouldCheckTableCreateDDL
     */
    public function setShouldCheckTableCreateDDL(bool $shouldCheckTableCreateDDL)
    {
        $this->shouldCheckTableCreateDDL = $shouldCheckTableCreateDDL;
    }

    /**
     * @return bool
     */
    public function isShouldCheckTableFieldsDefinition(): bool
    {
        return $this->shouldCheckTableFieldsDefinition;
    }

    /**
     * @param bool $shouldCheckTableFieldsDefinition
     */
    public function setShouldCheckTableFieldsDefinition(bool $shouldCheckTableFieldsDefinition)
    {
        $this->shouldCheckTableFieldsDefinition = $shouldCheckTableFieldsDefinition;
    }

    /**
     * @return bool
     */
    public function isShouldCheckTableRowCount(): bool
    {
        return $this->shouldCheckTableRowCount;
    }

    /**
     * @param bool $shouldCheckTableRowCount
     */
    public function setShouldCheckTableRowCount(bool $shouldCheckTableRowCount)
    {
        $this->shouldCheckTableRowCount = $shouldCheckTableRowCount;
    }

    /**
     * @return array
     */
    public function getBigTablesToAvoidRowCount(): array
    {
        return $this->bigTablesToAvoidRowCount;
    }

    /**
     * @param string[] $bigTablesToAvoidRowCount
     */
    public function setBigTablesToAvoidRowCount(array $bigTablesToAvoidRowCount)
    {
        $this->bigTablesToAvoidRowCount = $bigTablesToAvoidRowCount;
    }

    /**
     * @param null $tables
     * @throws \Exception
     */
    public function compareTableStructure($tables = null)
    {
        echo "Compare mission accepted, fetch tables first..." . PHP_EOL;
        //echo __METHOD__.'@'.__LINE__.PHP_EOL;
        $tablesOfA = $this->getTableNames($this->getDB(self::INSTANCE_CODE_A), $tables);
        //print_r($tablesOfA);

        //echo __METHOD__.'@'.__LINE__.PHP_EOL;
        $tablesOfB = $this->getTableNames($this->getDB(self::INSTANCE_CODE_B), $tables);
        //print_r($tablesOfB);

        echo "Check each table..." . PHP_EOL;

        $ptrA = 0;
        $ptrB = 0;
        while ($ptrA < count($tablesOfA) || $ptrB < count($tablesOfB)) {
            $currentTableNameOfA = ArkHelper::readTarget($tablesOfA, [$ptrA]);
            $currentTableNameOfB = ArkHelper::readTarget($tablesOfB, [$ptrB]);
            if ($ptrA >= count($tablesOfA)) {
                // only B
                echo "[ |B] Table [$currentTableNameOfB] exists in " . $this->getName(self::INSTANCE_CODE_B) . " but not in " . $this->getName(self::INSTANCE_CODE_A) . "!" . PHP_EOL;
                $ptrB++;
            } elseif ($ptrB > count($tablesOfB)) {
                // only A
                echo "[A| ] Table [$currentTableNameOfA] exists in " . $this->getName(self::INSTANCE_CODE_A) . " but not in " . $this->getName(self::INSTANCE_CODE_B) . "!" . PHP_EOL;
                $ptrA++;
            } else {
                // both here
                if ($currentTableNameOfA > $currentTableNameOfB) {
                    echo "[ |B] Table [$currentTableNameOfB] exists in " . $this->getName(self::INSTANCE_CODE_B) . " but not in " . $this->getName(self::INSTANCE_CODE_A) . "!" . PHP_EOL;
                    $ptrB++;
                } elseif ($currentTableNameOfB > $currentTableNameOfA) {
                    echo "[A| ] Table [$currentTableNameOfA] exists in " . $this->getName(self::INSTANCE_CODE_A) . " but not in " . $this->getName(self::INSTANCE_CODE_B) . "!" . PHP_EOL;
                    $ptrA++;
                } else {
                    //SAME
                    // check table ddl and data
                    if ($this->shouldCheckTableCreateDDL) {
                        $creationA = $this->getTableCreation($this->getDB(self::INSTANCE_CODE_A), $currentTableNameOfA);
                        $creationB = $this->getTableCreation($this->getDB(self::INSTANCE_CODE_B), $currentTableNameOfB);
                        if ($creationA != $creationB) {
                            echo "[A|B] Table [$currentTableNameOfA] exists in both schemes but differ!" . PHP_EOL;
                            echo "\tIn " . $this->getName(self::INSTANCE_CODE_A) . ":" . PHP_EOL . $creationA . PHP_EOL;
                            echo "\tIn " . $this->getName(self::INSTANCE_CODE_B) . ":" . PHP_EOL . $creationB . PHP_EOL;
                        }
                    }

                    if ($this->shouldCheckTableFieldsDefinition) {
                        $fieldsOfA = $this->getTableFields($this->getDB(self::INSTANCE_CODE_A), $currentTableNameOfA);
                        $fieldsOfB = $this->getTableFields($this->getDB(self::INSTANCE_CODE_B), $currentTableNameOfB);
                        //print_r($fieldsOfA); print_r($fieldsOfB);
                        $same = $this->checkTablesHaveSameFields($fieldsOfA, $fieldsOfB, $onlyInA, $onlyInB, $inBothButDiff);
                        if (!$same) {
                            //print_r(['only in A'=>$onlyInA, 'only in B'=> $onlyInB, 'diff'=> $inBothButDiff]);
                            if (!empty($onlyInA)) {
                                echo "[A| ] Fields only in scheme " . $this->getName(self::INSTANCE_CODE_A) . "'s table " . $currentTableNameOfA . PHP_EOL;
                                echo implode(PHP_EOL, $onlyInA) . PHP_EOL;
                            }
                            if (!empty($onlyInB)) {
                                echo "[ |B] Fields only in scheme " . $this->getName(self::INSTANCE_CODE_B) . "'s table " . $currentTableNameOfB . PHP_EOL;
                                echo implode(PHP_EOL, $onlyInB) . PHP_EOL;
                            }
                            if (!empty($inBothButDiff)) {
                                foreach ($inBothButDiff as $fieldName => $details) {
                                    echo "[A|B] Table [$currentTableNameOfA] field $fieldName differ:" . PHP_EOL;
                                    echo "\tIn " . $this->getName(self::INSTANCE_CODE_A) . " as " . $details[0] . PHP_EOL;
                                    echo "\tIn " . $this->getName(self::INSTANCE_CODE_B) . " as " . $details[1] . PHP_EOL;
                                }
                            }
                        }
                    }

                    if ($this->shouldCheckTableRowCount) {
                        if (in_array($currentTableNameOfA, $this->bigTablesToAvoidRowCount)) {
                            echo "[A|B] Table [$currentTableNameOfA] might be too big to check, passover..." . PHP_EOL;
                        } else {
                            $rowCountA = $this->getTableRowCount($this->getDB(self::INSTANCE_CODE_A), $currentTableNameOfA);
                            $rowCountB = $this->getTableRowCount($this->getDB(self::INSTANCE_CODE_B), $currentTableNameOfB);
                            if ($rowCountA != $rowCountB) {
                                echo "[A|B] Table [$currentTableNameOfA] rows differ:" . PHP_EOL;
                                echo "\tIn " . $this->getName(self::INSTANCE_CODE_A) . " as " . $rowCountA . PHP_EOL;
                                echo "\tIn " . $this->getName(self::INSTANCE_CODE_B) . " as " . $rowCountB . PHP_EOL;
                            }
                        }
                    }

                    $ptrA++;
                    $ptrB++;
                }
            }
        }

        echo "Mission completed." . PHP_EOL;
    }

    /**
     * @param ArkPDO $db
     * @param string[]|null $tables
     * @return string[]
     * @throws \Exception
     */
    protected function getTableNames($db, $tables = null)
    {
        $rows = $db->safeQueryAll("show tables", [], \PDO::FETCH_NUM);
        $ddl = array_column($rows, 0);
        if ($tables && is_array($tables)) $ddl = array_intersect($ddl, $tables);
        array_walk($ddl, function (&$item) {
            //echo "INSIDE array walk item $item key $key".PHP_EOL;
            $item = strtoupper($item);
        });
        sort($ddl);
        return $ddl;
    }

    /**
     * @param string $code
     * @return ArkPDO|null
     */
    protected function getDB($code)
    {
        return ArkHelper::readTarget($this->dbs, [$code]);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function getName($code)
    {
        return $this->getDB($code)->getPdoConfig()->title;
    }

    /**
     * @param ArkPDO $db
     * @param string $table
     * @return bool|mixed
     * @throws \Exception
     */
    protected function getTableCreation($db, $table)
    {
        $creation = $db->safeQueryAll("show create table " . $table, [], \PDO::FETCH_NUM);
        $creation = ArkHelper::readTarget($creation, [0, 1]);
        if (!$creation) throw new \Exception("cannot get create DDL of " . $table . ' but get ' . json_encode($creation));
        return $creation;
    }

    /**
     * @param ArkPDO $db
     * @param string $table
     * @return array
     * @throws \Exception
     */
    protected function getTableFields($db, $table)
    {
        $fields = $db->safeQueryAll("desc $table");
        return $fields;
    }

    /**
     * @param array $fieldsA
     * @param array $fieldsB
     * @param array $onlyInA
     * @param array $onlyInB
     * @param array $inBothButDiff
     * @return bool
     */
    protected function checkTablesHaveSameFields($fieldsA, $fieldsB, &$onlyInA = [], &$onlyInB = [], &$inBothButDiff = [])
    {
        $hashA = [];
        $hashB = [];
        $allFieldKeys = [];
        foreach ($fieldsA as $field) {
            $hash = $field['Field'] . ': ' . $field['Type'] . ', Null:' . $field['Null'] . ' Default:' . $field['Default'] . ' Key:' . $field['Key'] . ' Extra:' . $field['Extra'];
            $hashA[$field['Field']] = ($hash);
            $allFieldKeys[$field['Field']] = $field['Field'];
        }
        foreach ($fieldsB as $field) {
            $hash = $field['Field'] . ': ' . $field['Type'] . ', Null:' . $field['Null'] . ' Default:' . $field['Default'] . ' Key:' . $field['Key'] . ' Extra:' . $field['Extra'];
            $hashB[$field['Field']] = ($hash);
            $allFieldKeys[$field['Field']] = $field['Field'];
        }
        $allFieldKeys = array_keys($allFieldKeys);

        $onlyInA = [];
        $onlyInB = [];
        $inBothButDiff = [];
        foreach ($allFieldKeys as $fieldKey) {
            $fieldInA = ArkHelper::readTarget($hashA, [$fieldKey]);
            $fieldInB = ArkHelper::readTarget($hashB, [$fieldKey]);
            if ($fieldInA == $fieldInB) continue;
            if ($fieldInA && $fieldInB) {
                $inBothButDiff[$fieldKey] = [
                    ($fieldInA),
                    ($fieldInB)
                ];
            } elseif ($fieldInA === null) {
                $onlyInB[$fieldKey] = ($fieldInB);
            } elseif ($fieldInB === null) {
                $onlyInA[$fieldKey] = ($fieldInA);
            }
        }
        if (empty($onlyInA) && empty($onlyInB) && empty($inBothButDiff)) {
            return true;
        }
        return false;
    }

    /**
     * @param ArkPDO $db
     * @param string $table
     * @return int
     */
    protected function getTableRowCount($db, $table)
    {
        try {
            $count = $db->getOne("select count(*) from $table");
            return intval($count, 10);
        } catch (\Exception $exception) {
            return -1;
        }
    }
}