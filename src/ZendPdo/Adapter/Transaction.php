<?php

namespace ZendPdo\Adapter;

/**
 * classe Transaction
 * esta classe provê os métodos necessários para manipular transações
 */
abstract class Transaction {

    private static $conn; //Conexão ativa

    /**
     * método Open()
     * Abre uma transação e uma conexão ao BD
     * @param $options = nome do banco de dados
     */
    public static function Open($options)
    {
        //abre uma conexão e armazena na propriedade estática $conn
        if (empty(self::$conn)){
            self::$conn = Connection::Open($options);

            //inicia a transação
            self::$conn->beginTransaction();
        }
    }

    /**
     * método get()
     * retorna a conexão ativa da transação
     */
    public static function get()
    {
        //retorna a conexão ativa
        return self::$conn;
    }

    /**
     * método rollback()
     * desfaz todas operações realizadas na transação
     */
    public static function rollback()
    {
        if (self::$conn){
            //desfaz as operações realizads durante a transação
            self::$conn->rollback();
            self::$conn = null;
        }
    }

    /**
     * método close()
     * Aplica todas operações realizadas e fecha a transação
     */
    public static function close()
    {
        if (self::$conn){
            //aplica as operações realizada durante a transação
            self::$conn->commit();
            self::$conn = null;
        }
    }
}