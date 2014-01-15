<?php
namespace ZendPdo\Adapter;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Exception as DBException;

/**
 * Class AbstractDB
 * @package Base\Model
 */
abstract class AbstractDB
{

    protected $table;
    protected $db;

    /**
     * @param Adapter $db
     * @param $table
     */
    public function __construct(Adapter $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * findBy retorna um resultado de uma tabela
     *
     * @param array $where
     * @return array
     */
    public function findBy(Array $where = array())
    {
        try{
            $this->db->getDriver()->getConnection()->connect();
            $this->db->getDriver()->getConnection()->beginTransaction();

            $column = array_keys($where);
            $value = array_values($where);

            $whereColumn = $column[0];
            $whereValue = $this->db->getPlatform()->quoteTrustedValue($value[0]);

            //Comando SQL
            $sql = "SELECT * FROM {$this->table} WHERE {$whereColumn} = {$whereValue};";

            //Executando SQL
            $resultSet = new ResultSet();
            $result = $resultSet->initialize($this->db->getDriver()->getConnection()->execute($sql))->toArray();
            $this->db->getDriver()->getConnection()->disconnect();

            //Converte os Valores para UTF-8
            $encodedArray = array();
            foreach ($result as $value) {
                $encodedArray[] = array_map('trim',array_map('utf8_encode', $value));
            }

            return $encodedArray;

        }catch (DBException $e){
            $e->getTraceAsString();
            $this->db->getDriver()->getConnection()->rollback();
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
            $this->db->getDriver()->getConnection()->connect();
            $this->db->getDriver()->getConnection()->beginTransaction();

            //Comando SQL
            $sql = "SELECT * FROM {$this->table};";

            //Executando SQL
            $resultSet = new ResultSet();
            $result = $resultSet->initialize($this->db->getDriver()->getConnection()->execute($sql))->toArray();
            $this->db->getDriver()->getConnection()->disconnect();

            //Converte os Valores para UTF-8
            $encodedArray = array();
            foreach ($result as $value) {
                $encodedArray[] = array_map('trim',array_map('utf8_encode', $value));
            }

            return $encodedArray;

        }catch (DBException $e){
            $e->getTraceAsString();
            $this->db->getDriver()->getConnection()->rollback();
        }
    }

    /**
     * insert insere registro
     *
     * @param array $data
     * @param null $lastinsert
     * @return \Zend\Db\Adapter\Driver\StatementInterface|ResultSet
     */
    public function insert(Array $data = array(), $lastinsert = null)
    {
        try{
            $this->db->getDriver()->getConnection()->connect();
            $this->db->getDriver()->getConnection()->beginTransaction();

            //Filtando as entradas das colunas
            foreach(array_keys($data) as $value)
                $column[] = $value;

            //Filtando as entradas dos valores
            foreach(array_values($data) as $value)
                $values[] = $this->db->getPlatform()->quoteTrustedValue($value);

            $column = implode(',', $column);
            $value = implode(',', $values);

            //Comando SQL
            if ($lastinsert){

                $sql = "INSERT INTO {$this->table} ({$column}) VALUES ({$value})  returning {$lastinsert};";

                $resultSet = new ResultSet();
                $result = $resultSet->initialize($this->db->getDriver()->getConnection()->execute($sql))->toArray();
                $insert = $result[0][strtoupper($lastinsert)];

            }else{
                $sql = "INSERT INTO {$this->table} ({$column}) VALUES ({$value});";

                //Executando SQL
                $insert = $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
                $this->db->getDriver()->getConnection()->commit();
            }

            $this->db->getDriver()->getConnection()->disconnect();

            return $insert;

        }catch (DBException $e){
            $e->getTraceAsString();
            $this->db->getDriver()->getConnection()->rollback();
        }
    }

    /**
     * update atualiza registro
     *
     * @param array $data
     * @param array $where
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function update(Array $data = array(), Array $where = array())
    {
        try{
            $this->db->getDriver()->getConnection()->connect();
            $this->db->getDriver()->getConnection()->beginTransaction();

            //Filtando as entradas das colunas
            foreach($data as $column => $value)
                $line[] = $column . ' = '. $this->db->getPlatform()->quoteTrustedValue($value);

            $set = implode(',', $line);

            $column = array_keys($where);
            $value = array_values($where);

            $whereColumn = $column[0];
            $whereValue = $this->db->getPlatform()->quoteTrustedValue($value[0]);

            //Comando SQL
            $sql = "UPDATE {$this->table} SET {$set} WHERE {$whereColumn} = {$whereValue};";

            //Executando SQL
            $update = $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $this->db->getDriver()->getConnection()->commit();
            $this->db->getDriver()->getConnection()->disconnect();

            return $update;

        }catch (DBException $e){
            $e->getTraceAsString();
            $this->db->getDriver()->getConnection()->rollback();
        }
    }

    /**
     * delete detata registro
     *
     * @param array $where
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function delete(Array $where = array())
    {
        try{
            $this->db->getDriver()->getConnection()->connect();
            $this->db->getDriver()->getConnection()->beginTransaction();

            $column = array_keys($where);
            $value = array_values($where);

            $whereColumn = $column[0];
            $whereValue = $this->db->getPlatform()->quoteTrustedValue($value[0]);

            //Comando SQL
            $sql = "DELETE FROM {$this->table} WHERE {$whereColumn} = {$whereValue};";

            //Executando SQL
            $delete = $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
            $this->db->getDriver()->getConnection()->commit();
            $this->db->getDriver()->getConnection()->disconnect();

            if ($delete)
                return true;
            else
                return false;

        }catch (DBException $e){
            $e->getTraceAsString();
            $this->db->getDriver()->getConnection()->rollback();
        }
    }

}