<?php
namespace ZendPdo\Adapter;

use PDOException;

/**
 * Class AbstractDB
 * @package Base\Model
 */
abstract class AbstractDB
{

    protected $table;

    /**
     * @var $db \PDO
     */
    protected $db;

    /**
     * @param array $options
     * @param $table
     */
    public function __construct(Array $options, $table)
    {
        try{
            Transaction::Open($options);
            $this->db = Transaction::get();
            $this->table = $table;

        }catch (PDOException $e){
            echo $e->getTraceAsString();
            Transaction::rollback();
        }

    }

    /**
     * findBy retorna um resultado de uma tabela
     *
     * @param array $where
     * @return array
     */
    public function findBy(Array $where)
    {
        try{

            $column = array_keys($where);
            $value = array_values($where);

            //Comando SQL
            $sql = "SELECT * FROM {$this->table} WHERE  {$column[0]}= :value";

            //Executando SQL
            $query = $this->db->prepare($sql);
            $query->bindValue(":value", $value[0], \PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $query->closeCursor();
            Transaction::close();

            //Converte os Valores para UTF-8
            $encodedArray = array();
            foreach ($result as $value) {
                $encodedArray[] = array_map('trim',array_map('utf8_encode', $value));
            }
            return $encodedArray;

        }catch (PDOException $e){
            $e->getTraceAsString();
            Transaction::rollback();
        }
    }

    /**
     * findAll retorna todos os resultados de uma tabela
     *
     * @return array
     */
    public function findAll()
    {
        try{
            //Comando SQL
            $sql = "SELECT * FROM {$this->table};";

            //Executando SQL
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            $query->closeCursor();
            Transaction::close();

            //Converte os Valores para UTF-8
            $encodedArray = array();
            foreach ($result as $value) {
                $encodedArray[] = array_map('trim',array_map('utf8_encode', $value));
            }
            return $encodedArray;

        }catch (PDOException $e){
            echo $e->getTraceAsString();
            Transaction::rollback();
        }
    }


    /*
    public function insert(Array $data = array(), $lastinsert = null)
    {
        try{
            //Cria os parâmetros
            $param = array();
            for($x =1; $x <= count($data); $x++){
                $param[] = ":val".$x;
            }

            //Define colunas, valores e parâmetros
            $column = implode(',', array_keys($data));
            $value = array_values($data);
            $param = implode(',', array_values($param));

            //Comando SQL
            if ($lastinsert){

                $sql = "INSERT INTO {$this->table} ({$column}) VALUES ({$param})  returning {$lastinsert};";

                //Executando SQL
                $query = $this->db->prepare($sql);
                $y = 0;
                for($x =1; $x <= count($data); $x++){
                    $arg = mb_convert_encoding(mb_convert_case($value[$y],MB_CASE_UPPER, "UTF-8"),'ISO-8859-1', 'UTF-8');
                    $query->bindValue(":val".$x, $arg, \PDO::PARAM_STR);
                    $y++;
                }

                $query->execute();
                $result = $query->fetch(\PDO::FETCH_ASSOC);
                Transaction::close();

                return $result;

            }else{
                $sql = "INSERT INTO {$this->table} ({$column}) VALUES ({$param});";

                //Executando SQL
                $query = $this->db->prepare($sql);
                $y = 0;
                for($x = 1; $x <= count($data); $x++){
                    $arg = mb_convert_encoding(mb_convert_case($value[$y],MB_CASE_UPPER, "UTF-8"),'ISO-8859-1', 'UTF-8');
                    $query->bindValue(":val".$x, $arg, \PDO::PARAM_STR);
                    $y++;
                }
                $query->execute();
                Transaction::close();

                return true;
            }

        }catch (PDOException $e){
            echo $e->getTraceAsString();
            Transaction::rollback();
        }
    }

    public function update(Array $data = array(), Array $where = array())
    {
        try{
            //Define colunas, valores e parâmetros
            $column = array_keys($data);
            $value = array_values($data);
            $whereColumn = array_keys($where);
            $whereValue = array_values($where);

            $y = 0;
            for($x = 1; $x<= count($data); $x++){
                $line[] = $column[$y] . ' = '. ':val'.$x;
                $y++;
            }
            $set = implode(',', $line);

            //Comando SQL
            $sql = "UPDATE {$this->table} SET {$set} WHERE {$whereColumn[0]} = :valWhere;";

            //Executando SQL
            $query = $this->db->prepare($sql);
            $y = 0;
            for($x =1; $x <= count($data); $x++){
                $arg = mb_convert_encoding(mb_convert_case($value[$y],MB_CASE_UPPER, "UTF-8"),'ISO-8859-1', 'UTF-8');
                $query->bindValue(":val".$x, $arg, \PDO::PARAM_STR);
                $y++;
            }
            $query->bindParam(":valWhere", $whereValue[0], \PDO::PARAM_STR);
            $query->execute();
            Transaction::close();

            return true;


        }catch (PDOException $e){
            echo $e->getTraceAsString();
            Transaction::rollback();
        }
    }
    */

    /**
     * delete detata registro
     *
     * @param array $where
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function delete(Array $where = array())
    {
        try{
            //Verifica se registro existe na base de dados
            if ($this->findBy($where)){

                $column = array_keys($where);
                $value = array_values($where);

                $whereColumn = $column[0];
                $whereValue = $value[0];

                //Comando SQL
                $sql = "DELETE FROM {$this->table} WHERE {$whereColumn} = :val;";

                //Executando SQL
                $delete = $this->db->prepare($sql);
                $delete->bindParam(":val", $whereValue, \PDO::PARAM_STR);
                return $delete->execute();
            }

            return false;

        }catch (PDOException $e){
            echo $e->getTraceAsString();
            Transaction::rollback();
        }
    }
}