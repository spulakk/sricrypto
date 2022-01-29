<?php
declare(strict_types=1);

namespace Database;

class Table
{
    private string $host;

    private string $user;

    private string $password;

    private string $database;

    private string $table;

    private \mysqli $connection;


    public function __construct(string $table)
    {
        $config = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'database.json');
        $configArray = json_decode($config);

        $this->host = $configArray->host;
        $this->user = $configArray->user;
        $this->password = $configArray->password;
        $this->database = $configArray->database;
        $this->table = $table;
        $this->connection = $this->getConnection();
    }


    public function __destruct()
    {
        $this->connection->close();
    }


    public function select(array $select, array $where = [], bool $all = false): ?array
    {
        $selectArray = [];

        $select = $this->sanitize($select);

        foreach($select as $column)
        {
            $selectArray[] = "`$column`";
        }

        $selectString = implode(', ', $selectArray);

        $query = "SELECT $selectString FROM `$this->table`";

        if($where)
        {
            $whereArray = [];

            $where = $this->sanitize($where);

            foreach($where as $condition => $value)
            {
                $column = explode(' ', $condition)[0];
                $condition = str_replace($column, "`$column`", $condition);

                if(is_array($value))
                {
                    foreach($value as $key => $val)
                    {
                        $value[$key] = "'$val'";
                    }

                    $valueString = implode(', ', $value);
                }
                else
                {
                    $value = $value === 'NULL' ? "$value" : "'$value'";
                }

                $whereArray[] = str_replace('?', $valueString ?? $value, $condition);
            }

            $whereString = implode(' AND ', $whereArray);

            $query .= " WHERE $whereString";
        }

        return $all ? $this->connection->query($query)->fetch_all() : $this->connection->query($query)->fetch_assoc();
    }


    public function fetchPairs(string $key, string $value, array $where = [], ?string $order = null): ?array
    {
        $key = $this->sanitize($key);
        $value = $this->sanitize($value);

        $query = "SELECT `$key`, `$value` FROM `$this->table`";

        if($where)
        {
            $whereArray = [];

            $where = $this->sanitize($where);

            foreach($where as $condition => $value)
            {
                $column = explode(' ', $condition)[0];
                $condition = str_replace($column, "`$column`", $condition);

                if(is_array($value))
                {
                    foreach($value as $key => $val)
                    {
                        $value[$key] = "'$val'";
                    }

                    $valueString = implode(', ', $value);
                }
                else
                {
                    $value = $value === 'NULL' ? "$value" : "'$value'";
                }

                $whereArray[] = str_replace('?', $valueString ?? $value, $condition);
            }

            $whereString = implode(' AND ', $whereArray);

            $query .= " WHERE $whereString";
        }

        if($order)
        {
            $direction = null;

            if(str_contains($order, ' ASC'))
            {
                $order = str_replace(' ASC', '', $order);
            }
            elseif(str_contains($order, ' DESC'))
            {
                $order = str_replace(' DESC', '', $order);
                $direction = 'DESC';
            }

            $query .= " ORDER BY `$order`";

            if($direction)
            {
                $query .= " $direction";
            }
        }

        $resultArray = $this->connection->query($query)->fetch_all();

        $returnArray = [];

        foreach($resultArray as $result)
        {
            $returnArray[$result[0]] = $result[1];
        }

        return $returnArray;
    }


    public function insert(array $data): void
    {
        $columnArray = [];
        $valueArray = [];

        $data = $this->sanitize($data);

        foreach($data as $column => $value)
        {
            $columnArray[] = "`$column`";
            $valueArray[] = "'$value'";
        }

        $columnString = implode(', ', $columnArray);
        $valueString = implode(', ', $valueArray);

        $this->connection->query("INSERT INTO `$this->table` ($columnString) VALUES ($valueString)");
    }


    public function insertMultiple(array $dataArray): void
    {
        if(!empty($dataArray))
        {
            $query = '';

            foreach($dataArray as $data)
            {
                $columnArray = [];
                $valueArray = [];

                $data = $this->sanitize($data);

                foreach($data as $column => $value)
                {
                    $columnArray[] = "`$column`";
                    $valueArray[] = "'$value'";
                }

                $columnString = implode(', ', $columnArray);
                $valueString = implode(', ', $valueArray);

                $query .= "INSERT INTO `$this->table` ($columnString) VALUES ($valueString);";
            }

            $this->connection->multi_query($query);
        }
    }


    public function update(array $data, array $where = []): void
    {
        $updateArray = [];

        $data = $this->sanitize($data);

        foreach($data as $column => $value)
        {
            $updateArray[] = "`$column` = '$value'";
        }

        $updateString = implode(', ', $updateArray);

        $query = "UPDATE `$this->table` SET $updateString";

        if($where)
        {
            $whereArray = [];

            $where = $this->sanitize($where);

            foreach($where as $condition => $value)
            {
                $column = explode(' ', $condition)[0];
                $condition = str_replace($column, "`$column`", $condition);

                if(is_array($value))
                {
                    foreach($value as $key => $val)
                    {
                        $value[$key] = "'$val'";
                    }

                    $valueString = implode(', ', $value);
                }
                else
                {
                    $value = $value === 'NULL' ? "$value" : "'$value'";
                }

                $whereArray[] = str_replace('?', $valueString ?? $value, $condition);
            }

            $whereString = implode(' AND ', $whereArray);

            $query .= " WHERE $whereString";
        }

        $this->connection->query($query);
    }


    public function delete(array $where = []): void
    {
        $query = "DELETE FROM `$this->table`";

        if($where)
        {
            $whereArray = [];

            $where = $this->sanitize($where);

            foreach($where as $condition => $value)
            {
                $column = explode(' ', $condition)[0];
                $condition = str_replace($column, "`$column`", $condition);

                if(is_array($value))
                {
                    foreach($value as $key => $val)
                    {
                        $value[$key] = "'$val'";
                    }

                    $valueString = implode(', ', $value);
                }

                $whereArray[] = str_replace('?', $valueString ?? $value, $condition);
            }

            $whereString = implode(' AND ', $whereArray);

            $query .= " WHERE $whereString";
        }

        $this->connection->query($query);
    }


    private function getConnection(): \mysqli
    {
        $connection = new \mysqli($this->host, $this->user, $this->password, $this->database);

        if($connection->connect_error)
        {
            die("database Connection Error, Error No.: " . $connection->connect_errno . " | " . $connection->connect_error);
        }

        return $connection;
    }


    private function sanitize($data)
    {
        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                if(is_string($value))
                {
                    $data[$key] = $this->connection->real_escape_string($value);
                }
            }
        }
        elseif(is_string($data))
        {
            $data = $this->connection->real_escape_string($data);
        }

        return $data;
    }
}
