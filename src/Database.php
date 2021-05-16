<?php

namespace Hendricks\Litebase;

use PDO;
use Exception;

class Database extends StaticQuery
{

    private $connexion;

    private $table;

    private $result;

    private $queryResult;

    private $format = null;

    //private $params = [];

    private $_sqlData;

    protected $_fields;

    protected $_where;

    protected $_order;

    protected $_limit;

    protected $_sqlType;

    protected $query;

    protected $_fetch = null;

    protected $_fetchAll = null;

    private static $_instance;


    public function __construct(string $table = null, array $config = null)
    {
        if ($config) {
            $this->connexion = (new Connexion($config))->getConnexion();
        } else {
            $this->connexion = Connexion::getInstance()->getConnexion();
        }

        if ($table) {
            $this->table = strtolower($table);
        }

        self::$_instance = $this;
    }


    /**
     * getInstance
     * retoune l'instance de la class
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    /**
     * fillDatabase
     * Enregistre toutes les données sql dans la base de donnée
     * @param  mixed $datas
     * @return void
     */
    public function fillDatabase(string $datas)
    {
        $status = $this->connexion->exec($datas);
        if ($status == 1) {
            throw new Exception("Error : Fail to create database structure", 1);
        }
        return true;
    }

    /**
     * Récupère des enregistrements
     *
     * @param string $fields
     * @return Database
     */
    public function select($fields = '*'): self
    {
        $this->_fields = $fields;
        $this->_sqlType = 'select';

        return $this;
    }

    /**
     * Crée un nouvel enregistrement.
     *
     * @param array $fields
     * @return Database
     */
    public function insert(array $fields): self
    {
        $this->_sqlData = $fields;
        $this->_sqlType = 'insert';
        return $this;
    }


    /**
     * Met à jour un enregistrement
     *
     * @param array $fields
     * @return Database
     */
    public function update(array $fields): self
    {
        foreach ($fields as $column => $value) {
            if ($value === null) {
                $value = "NULL";
            } else {
                $value = '"' . $value . '"';
            }

            $field_array[] = '`' . $column . '` = ' . $value;
        }

        $this->_sqlData = implode(',', $field_array);
        $this->_sqlType = 'update';
        return $this;
    }

    /**
     * Supprime un enregistrement.
     *
     * @return Database
     */
    public function delete(): self
    {
        $this->_sqlType = 'delete';
        return $this;
    }

    /**
     * Compte le nombre d'enregistrement.
     *
     * @param string $fields
     * @return Database
     */
    public function count($fields = '*'): self
    {
        $this->_fields = $fields;
        $this->_sqlType = 'count';
        return $this;
    }

    /**
     * Permet de définir des conditions pour éffectuer des
     * requêtes sur la table
     *
     * @param array $where
     * @return Database
     */
    public function where($where = null): self
    {
        if (!is_null($where)) {
            $this->_where = $where;
        }

        return $this;
    }

    /**
     * Permet de définir l'ordre de récupération des informations.
     *
     * @param string $order
     * @return Database
     */
    public function orderBy($order = null): self
    {
        if (!is_null($order)) {
            $this->_order = $order;
        }

        return $this;
    }

    /**
     * Permet de limiter les informations récupérées.
     *
     * @param string $limit
     * @return Database
     */
    public function limit($limit = null): self
    {
        if (!is_null($limit)) {
            $this->_limit = $limit;
        }

        return $this;
    }

    /**
     * fetch
     * @return Database
     */
    public function fetch(): self
    {
        $this->_fetch = true;
        return $this;
    }

    /**
     * fetch
     * @return Database
     */
    public function fetchAll(): self
    {
        $this->_fetchAll = true;
        return $this;
    }

    /**
     * buildQuery
     * Permet de construire la requête selon le type
     * @return Database
     */
    public function buildQuery()
    {
        if ($this->_sqlType == 'count') {
            $sql = "SELECT " . $this->_fields . " FROM " . $this->table;

            if ($this->_where) {
                $sql .= " WHERE " . $this->_where;
            }
            if ($this->_order) {
                $sql .= " ORDER BY " . $this->_order;
            }
            if ($this->_limit) {
                $sql .= " LIMIT " . $this->_limit;
            }
        } elseif ($this->_sqlType == 'select') {
            $sql = "SELECT " . $this->_fields;
            $sql .= " FROM " . $this->table;

            if ($this->_where) {
                $sql .= " WHERE " . $this->_where;
            }
            if ($this->_order) {
                $sql .= " ORDER BY " . $this->_order;
            }
            if ($this->_limit) {
                $sql .= " LIMIT " . $this->_limit;
            }
        } elseif ($this->_sqlType == 'delete') {
            $sql = "DELETE FROM " . $this->table;

            if ($this->_where) {
                $sql .= " WHERE " . $this->_where;
            }
        } elseif ($this->_sqlType == 'insert') {
            $columns = [];
            $values = [];

            foreach ($this->_sqlData as $column => $value) {
                $columns[] = '`' . $column . '`';

                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    $values[] = '"' . $value . '"';
                }
            }

            $sql = "INSERT INTO `" . $this->table . "` (" . implode(',', $columns) . ")";
            $sql .= " VALUES (" . implode(',', $values) . ")";
        } elseif ($this->_sqlType == 'update') {
            $sql = "UPDATE " . $this->table . " SET ";
            $sql .= $this->_sqlData;

            if ($this->_where) {
                $sql .= " WHERE " . $this->_where;
            }
        }

        $this->query = $sql;
        // return $sql;
    }

    /**
     * execute
     * Execute la requette sql
     */
    public function execute()
    {
        try {
            $this->buildQuery();

            $req = $this->connexion->prepare($this->query);
            $this->queryResult = $req->execute();

            if ($this->_sqlType == 'count') {
                $data = $req->rowCount();
            } elseif ($this->_sqlType == 'select') {
                if ($this->_fetch === true) {
                    $data = $req->fetch();
                } elseif ($this->_fetchAll === true) {
                    $data = $req->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $data = $req->fetch();
                }
            } elseif ($this->_sqlType == 'insert') {
                $data = $this->queryResult;
            } elseif ($this->_sqlType == 'update') {
                $data = $this->queryResult;
            }

            $req->closeCursor();

            if (!is_null($this->format)) {
                if ($this->format == 'json') {
                    $this->result = json_encode($data, JSON_PRETTY_PRINT);
                }
            } else {
                $this->result = $data;
            }

            return $this->result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * getResult
     * Permet d'obtenir le resultat de la requette
     * @return void
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * toJson
     * retourne le resultat en json
     * @return self
     */
    public function toJson(): self
    {
        $this->format = 'json';
        return $this;
    }
}
