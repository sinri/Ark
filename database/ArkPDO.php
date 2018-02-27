<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/14
 * Time: 13:37
 */

namespace sinri\ark\database;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;

class ArkPDO
{
    /**
     * @var ArkPDOConfig
     */
    protected $pdoConfig;
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var ArkLogger
     */
    protected $logger;

    /**
     * ArkPDO constructor.
     */
    public function __construct()
    {
        $this->logger = ArkLogger::makeSilentLogger();
        $this->pdoConfig = null;
    }

    /**
     * Connect to Database and make self::pdo an instance.
     * @throws \Exception
     */
    public function connect()
    {
        if (!is_a($this->pdoConfig, ArkPDOConfig::class)) {
            throw new \Exception("Ark PDO Config not given!");
        }

        $engine = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_ENGINE, ArkPDOConfig::ENGINE_MYSQL);
        $host = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_HOST);
        $port = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_PORT);
        $username = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_USERNAME);
        $password = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_PASSWORD);
        $database = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_DATABASE);
        $charset = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_CHARSET, ArkPDOConfig::CHARSET_UTF8);
        $options = $this->pdoConfig->getConfigField(ArkPDOConfig::CONFIG_OPTIONS, null);

        ArkHelper::assertItem($host, 'Ark PDO: Host is empty.');
        ArkHelper::assertItem($port, 'Ark PDO: Port is empty.');
        ArkHelper::assertItem($username, 'Ark PDO: Username is empty.');
        ArkHelper::assertItem($password, 'Ark PDO: Password is empty.');
        //ArkHelper::assertItem($database, 'Ark PDO: Database is empty.');
        ArkHelper::assertItem($charset, 'Ark PDO: CharSet is empty.');

        $engine = strtolower($engine);
        switch ($engine) {
            case ArkPDOConfig::ENGINE_MYSQL:
                if ($options === null) {
                    $options = [
                        \PDO::ATTR_EMULATE_PREPARES => false
                    ];
                }
                $this->pdo = new \PDO(
                    "mysql:host={$host};port={$port};charset={$charset}",//dbname={$database}; seems optional
                    $username,
                    $password,
                    $options
                );
                if (!empty($database)) {
                    $this->pdo->exec("use `{$database}`;");
                }
                if (!empty($charset)) {
                    $this->pdo->query("set names " . $charset);
                }
                break;
            default:
                throw new \Exception("Ark PDO: unsupported engine " . $engine);
                break;
        }
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ArkPDOConfig $pdoConfig
     */
    public function setPdoConfig(ArkPDOConfig $pdoConfig)
    {
        $this->pdoConfig = $pdoConfig;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param $sql
     * @param bool $usePrepare
     * @return \PDOStatement
     * @throws \Exception
     */
    protected function buildPDOStatement($sql, $usePrepare = false)
    {
        if ($usePrepare) {
            $statement = $this->pdo->prepare($sql);
        } else {
            $statement = $this->pdo->query($sql);
        }
        if (!$statement) {
            $this->logger->error("PDO Statement Building Failure Occurred.", ["sql" => $sql]);
            throw new \Exception("PDO Statement Building Failure Occurred: " . $sql);
        } else {
            $this->logger->debug("PDO Statement Generated.", ["sql" => $sql]);
        }
        return $statement;
    }

    /**
     * @param $sql
     * @return array
     * @throws \Exception
     */
    public function getAll($sql)
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * @param $sql
     * @param null $field
     * @return array
     * @throws \Exception
     */
    public function getCol($sql, $field = null)
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_BOTH);
        if ($field === null) $field = 0;
        $col = array_column($rows, $field);
        return $col;
    }

    /**
     * @param $sql
     * @return array|bool
     * @throws \Exception
     */
    public function getRow($sql)
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) < 1) return false;
        $row = $rows[0];
        return $row;
    }

    /**
     * @param $sql
     * @return mixed|bool
     * @throws \Exception
     */
    public function getOne($sql)
    {
        $stmt = $this->buildPDOStatement($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!is_array($rows) || count($rows) < 1) return false;
        $row = $rows[0];
        if (!is_array($row) || count($row) < 1) return false;
        $row = array_values($row);
        return $row[0];
    }

    /**
     * @param $sql
     * @return int|false affected row count(might be zero anyway), or false on error
     */
    public function exec($sql)
    {
        $this->logger->debug("Ready to execute sql", ["sql" => $sql]);
        $rows = $this->pdo->exec($sql);
        return $rows;
    }

    /**
     * @param $sql
     * @param null $pk
     * @return bool|string
     */
    public function insert($sql, $pk = null)
    {
        $this->logger->debug("Ready to execute insert sql", ["sql" => $sql]);
        $rows = $this->pdo->exec($sql);
        if ($rows) {
            return $this->pdo->lastInsertId($pk);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @return mixed
     */
    public function getPDOErrorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * @return array
     */
    public function getPDOErrorInfo()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @param $string
     * @param int $parameterType \PDO::PARAM_STR or \PDO::PARAM_INT
     * @return string
     */
    public function quote($string, $parameterType = \PDO::PARAM_STR)
    {
        if (!$this->pdo) {
            if ($parameterType == \PDO::PARAM_INT) {
                return intval($string);
            }
            return self::dryQuote($string);
        }
        return $this->pdo->quote($string, $parameterType);
    }

    /**
     * @since 2.1.11
     * @param $inp
     * @return array|mixed
     */
    public static function dryQuote($inp)
    {
        if (is_array($inp))
            return array_map([__CLASS__, __METHOD__], $inp);

        if (!empty($inp) && is_string($inp)) {
            $x = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
            return "'{$x}'";
        }

        return $inp;
    }

    /**
     * @param $sql
     * @param array $values
     * @param int $fetchStyle
     * @return array
     * @throws \Exception
     */
    public function safeQueryAll($sql, $values = array(), $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $sth = $this->buildPDOStatement($sql, true);
        $sth->execute($values);
        $rows = $sth->fetchAll($fetchStyle);
        return $rows;
    }

    /**
     * @param $sql
     * @param array $values
     * @return mixed
     * @throws \Exception
     */
    public function safeQueryRow($sql, $values = array())
    {
        $sth = $this->buildPDOStatement($sql, true);
        if ($sth->execute($values)) {
            $row = $sth->fetch(\PDO::FETCH_ASSOC);
            return $row;
        }
        return false;
    }

    /**
     * @param $sql
     * @param array $values
     * @return string
     * @throws \Exception
     */
    public function safeQueryOne($sql, $values = array())
    {
        $sth = $this->buildPDOStatement($sql, true);
        if ($sth->execute($values)) {
            $col = $sth->fetchColumn(0);
            return $col;
        }
        return false;
    }

    /**
     * @param $sql
     * @param array $values
     * @param int $insertedId
     * @param null $pk
     * @return bool
     * @throws \Exception
     */
    public function safeInsertOne($sql, $values = array(), &$insertedId = 0, $pk = null)
    {
        $sth = $this->buildPDOStatement($sql, true);
        $done = $sth->execute($values);
        if ($done)
            $insertedId = $this->pdo->lastInsertId($pk);
        return $done;
    }

    /**
     * @param $sql
     * @param array $values
     * @param null $sth @since 1.3.3
     * @return bool
     * @throws \Exception
     */
    public function safeExecute($sql, $values = array(), &$sth = null)
    {
        $sth = $this->buildPDOStatement($sql, true);
        $done = $sth->execute($values);
        return $done;
    }

    /**
     * @since 1.3.3
     * @param null|string $pk
     * @return string
     */
    public function getLastInsertID($pk = null)
    {
        return $this->pdo->lastInsertId($pk);
    }

    /**
     * PDOStatement::rowCount() 返回上一个由对应的 PDOStatement 对象执行DELETE、 INSERT、或 UPDATE 语句受影响的行数。
     * 如果上一条由相关 PDOStatement 执行的 SQL 语句是一条 SELECT 语句，有些数据可能返回由此语句返回的行数。
     * 但这种方式不能保证对所有数据有效，且对于可移植的应用不应依赖于此方式。
     * @param \PDOStatement $statement
     * @return int
     */
    public function getAffectedRowCount($statement)
    {
        return $statement->rowCount();
    }

    /**
     * 比PDO更加丧心病狂的SQL模板
     * @since 2.1.11
     *  Sample SQL:
     * select key_field,value,`?`
     * from `?`.`?`
     * where key_field in (?)
     * and status = ?
     * limit [?] , [?]
     *  RULE:
     * (1) `?` => $p
     * (2)  ?  => quote($p)
     * (3) (?) => (quote($p[]),...)
     * (4) [?] => integer_value($p)
     * (5) {?} => float_value($p)
     * @param $template
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    public function safeBuildSQL($template, $parameters = [])
    {
        $this->logger->debug($template, ['parameters' => $parameters]);
        $count = preg_match_all('/\?|`\?`|\(\?\)|\[\?\]|\{\?\}/', $template, $matches, PREG_OFFSET_CAPTURE);
        $this->logger->debug("preg_match_all count=" . json_encode($count), ['matches' => $matches]);
        if ($count === 0) {
            return $template;
        }
        if (!$count) {
            throw new \Exception("The sql template is not correct.");
        }
        if ($count != count($parameters)) {
            throw new \Exception("The sql template has not correct number of parameters.");
        }

        $parts = [];
        $currentIndex = 0;
        for ($x = 0; $x < $count; $x++) {
            $sought = $matches[0][$x];
            $keyword = $sought[0];
            $index = $sought[1];

            if ($index != $currentIndex) {
                $piece = substr($template, $currentIndex, $index - $currentIndex);
                //$this->debug(__METHOD__.'@'.__LINE__." piece: ".$piece,[$currentIndex,($index - $currentIndex)]);
                $parts[] = $piece;
                $currentIndex = $index;
                //$this->debug(__METHOD__.'@'.__LINE__." current index -> ".$currentIndex);
            }
            $parts[] = $keyword;
            $currentIndex = $currentIndex + strlen($keyword);
            //$this->debug(__METHOD__.'@'.__LINE__." piece: ",$keyword);
            //$this->debug(__METHOD__.'@'.__LINE__." current index -> ",$currentIndex);
        }
        if ($currentIndex < strlen($template)) {
            $piece = substr($template, $currentIndex);
            $parts[] = $piece;
            //$this->debug(__METHOD__ . '@' . __LINE__ . " piece: ", $piece);
        }

        $this->logger->debug("parts", ['parts' => $parts]);

        $sql = "";
        $ptr = 0;
        foreach ($parts as $part) {
            switch ($part) {
                // RULE:
                // (1) `?` => $p
                case '`?`':
                    {
                        $sql .= '`' . $parameters[$ptr] . '`';
                        $ptr++;
                    }
                    break;
                // (2)  ?  => quote($p)
                case '?':
                    {
                        $sql .= $this->quote($parameters[$ptr]);
                        $ptr++;
                    }
                    break;
                // (3) (?) => (quote($p[]),...)
                case '(?)':
                    {
                        if (is_array($parameters[$ptr])) {
                            $group = [];
                            foreach ($parameters[$ptr] as $object) {
                                $group[] = $this->quote($object);
                            }
                            $sql .= '(' . implode(",", $group) . ')';
                        } else {
                            $sql .= '(' . $parameters[$ptr] . ')';
                        }
                        $ptr++;
                    }
                    break;
                // (4) [?] => int val of ($p)
                case '[?]':
                    {
                        $sql .= intval($parameters[$ptr], 10);
                        $ptr++;
                    }
                    break;
                // (5) {?} => float val of ($p)
                case '{?}':
                    {
                        $sql .= floatval($parameters[$ptr]);
                        $ptr++;
                    }
                    break;
                default:
                    $sql .= $part;
            }
        }

        return $sql;
    }

}