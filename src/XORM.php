<?php

namespace XORM;

class XORM extends \PDO
{

    private $host, $username, $password, $table, $select, $limit, $islem, $delete, $insert, $update, $order_by, $sql;
    private $where = null;


    public function __construct($host, $username, $password)
    {

        $this->host = $host;
        $this->username = $username;
        $this->password = $password;

        parent::__construct($this->host, $this->username, $this->password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    }

    public function table($param)
    {
        $this->table = $param;
        return $this;
    }

    public function select($param)
    {

        $this->islem = "SELECT";

        $this->select = $param;

        return $this;
    }

    public function delete($param)
    {

        $this->islem = "DELETE";

        $this->delete = $param;
        return $this;
    }

    public function insert($param)
    {

        $this->islem = "INSERT";
        $this->insert = $param;
        return $this;
    }

    public function sql($param)
    {
        $this->islem = "SQL";
        $this->sql = $param;
        return $this;
    }

    public function where($param)
    {

        $count = count($param);

        if ($count > 1) {

            $this->where = "WHERE ";

            foreach ($param as $key => $value) {
                if ($key === array_key_last($param)) {
                    //$this->where .= $key . '= "' . $value .'"';
                    $this->where .= $key . '= ' . $value . '';
                } else {
                    //$this->where .= $key . '= "' . $value .'" AND ';
                    $this->where .= $key . '= ' . $value . ' AND ';
                }
            }
        } else {

            //$this->where = 'WHERE ' . key($param) . '="' . $param[key($param)] .'"';
            $this->where = 'WHERE ' . key($param) . '=' . $param[key($param)] . '';
        }

        return $this;
    }


    public function update($param)
    {

        $this->islem = "UPDATE";


        $this->update = call_user_func_array("array_merge", $param);


        return $this;
    }

    public function limit($param)
    {


        if (count($param) == 2) {

            $this->limit = "LIMIT " . $param[0] . ',' . $param[1];
        } else {

            $this->limit = "LIMIT " . $param;
        }
        return $this;
    }

    public function order_by($param)
    {

        $this->order_by = "ORDER BY " . $param;

        return $this;
    }

    public function run()
    {

        switch ($this->islem) {

            case "SELECT":

                $sql = "SELECT $this->select FROM $this->table $this->where $this->order_by $this->limit";
                $data = $this->prepare($sql);
                $data->execute();

                return $data->fetchAll(\PDO::FETCH_ASSOC);
                break;

            case "INSERT":

                $column = array_keys($this->insert);
                $column = implode(',', $column);


                $values = str_repeat("?,", count($this->insert));
                $values = rtrim($values, ",");

                $sql = "INSERT INTO $this->table ($column) VALUES ($values)";
                //echo $sql;

                $insert = $this->prepare($sql);

                $i = 1;

                foreach ($this->insert as $key => &$value) {

                    $insert->bindParam($i++, $value, \PDO::PARAM_STR);
                }

                $insert->execute();

                $id = $this->lastInsertId();

                return $id;

                break;


            case "UPDATE":


                $set = array_keys($this->update);


                foreach ($set as $key => $value) {

                    $set__[] = $value . ' = ' . ":$value";
                }

                $set__ = implode(',', $set__);

                $sql = "UPDATE $this->table SET $set__ $this->where";

                $update = $this->prepare($sql);

                $update->execute($this->update);


                break;

            case "DELETE":

                $key = key($this->delete);
                $value = current($this->delete);

                $sql = "DELETE FROM $this->table WHERE $key = $value";

                $delete = $this->prepare($sql);
                $delete->execute();

                break;

            case "SQL":


                $data = $this->prepare($this->sql);
                $data->execute();

                return $data->fetchAll(\PDO::FETCH_ASSOC);

                break;
        }
    }
}
