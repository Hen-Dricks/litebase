<?php

namespace Hendricks\Litebase;

use PDO;

class Connexion
{

    private $config = [];

    private $connexion;

    private static $_instance;

    private $current_config_name;


    public function __construct(array $config = null)
    {
        if ($config) {
            $this->setConfig($config);
            $this->setCurrentConfigName('custom_user_config');
        } else {
            $this->setConfig();
            $this->setCurrentConfigName('app_config');
        }

        $this->connect();
    }


    /**
     * setCurrentConfigName
     * Permet de définir le nom de la configuration courante
     *
     * @param string $name
     * @return void
     */
    public function setCurrentConfigName(string $name)
    {
        $this->current_config_name = $name;
    }

    /**
     * getConfigurationName
     * Permet de récuperer une configuration
     *
     * @param string $name
     * @return array
     */
    public function getConfigurationName()
    {
        return $this->current_config_name;
    }


    /**
     * addConfiguration
     * Permet d'ajouter une nouvelle configuration de connection
     *
     * @param string $name
     * @param Array $data
     * @return void
     */
    public function addConfiguration(string $name, string $data)
    {
        $this->config[$name] = $data;
    }


    /**
     * getTablePrefix
     * retourne le prefix de la configuration
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->config['prefix'];
    }


    /**
     * getDriver
     * Permet de récuperer le driver de la configuration courante
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->config['driver'];
    }


    /**
     * getInstance
     * retoune l'instance de la class
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            return new Connexion();
        }

        return self::$_instance;
    }


    /**
     * connect
     * Retourne une nouvelle instance de PDO
     * 
     * @return void
     */
    private function connect()
    {
        try {
            $this->connexion = new PDO('mysql:host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['database'], $this->config['username'], $this->config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * setConfig
     * Permet de definir la configuration de la base de Données 
     * 
     * @param  mixed $config
     * @return void
     */
    private function setConfig(array $config = null)
    {
        if ($config) {
            $this->config = [
                'driver' => $config['driver'],
                'host' => $config['host'],
                'port' => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
                'charset' => $config['charset'],
                'collation' => $config['collation'],
                'prefix' => $config['prefix']
            ];
        } else {

            $connexion = require HORIZOM_CONFIG . 'database.php';
            $connexion_config = $connexion['database.connections'][$connexion['database.default']];

            $this->config = [
                'driver' => $connexion_config['driver'],
                'host' => $connexion_config['host'],
                'port' => $connexion_config['port'],
                'database' => $connexion_config['database'],
                'username' => $connexion_config['username'],
                'password' => $connexion_config['password'],
                'charset' => $connexion_config['charset'],
                'collation' => $connexion_config['collation'],
                'prefix' => $connexion_config['prefix']
            ];
        }
    }


    /**
     * getConnexion
     * retourne la connexion
     * 
     * @return void
     */
    public function getConnexion()
    {
        return ($this->connexion instanceof PDO) ? $this->connexion : $this->connect();
    }
}
