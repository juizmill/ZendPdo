<?php
namespace ZendPdo\Adapter;

use \PDO;
use \PDOException;

/**
 * classe Connection
 * Gerencia conexão com o banco de dados através de arquivos de configuração.
 */
abstract class Connection
{
    const TYPE = "firebird";

    public static function open(Array $option = array())
    {

        //Lê as informações
        $user = isset($option['username']) ? $option['username'] : NULL;
        $pass = isset($option['password']) ? $option['password'] : NULL;
        $name = isset($option['name']) ? $option['name'] : NULL;
        $host = isset($option['host']) ? $option['host'] : NULL;
        $port = isset($option['port']) ? $option['port'] : NULL;
        $dsn = isset($option['dsn']) ? $option['dsn'] : NULL;


        try {

            //Descobre qual o tipo (driver) de banco de dados a ser utilizado
            switch (self::TYPE) {

                #Conecta com o MYSQL
                case 'mysql':
                    $port = $port ? $port : '3306';
                    $conn = new PDO("mysql:host={$host}; port={$port}; dbname={$name}", $user, $pass, array(PDO::ATTR_PERSISTENT => true));
                    break;

                #Conecta com o FIREBIRD
                case 'firebird':
                    if($dsn)
                        $conn = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true));
                    else
                        $conn = new PDO("firebird:dbname={$host}:{$name}", $user, $pass, array(PDO::ATTR_PERSISTENT => true));
                    break;
            }

            #Define para que o PDO lance exceções na ocorrência de erros
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            #retorna o objeto instanciado
            return $conn;
        } catch (PDOException $e) {
            echo $e->getTraceAsString();
        }

    }
}