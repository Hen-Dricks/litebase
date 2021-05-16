<?php

namespace Hendricks\Litebase;

use \PDO;
use \Exception;
use Hendricks\Litebase\Connexion;

class StaticQuery
{

    /**
     * connexion
     * Etablie la liaison avec la connexion et retourne une instance de PDO
     */
    private static function connexion()
    {
        return Connexion::getInstance()->getConnexion();
    }


    /**
     * queryGetFetchAll
     *  fais un fecthAll classic et retourne les resultats sous forme de tableau assoc
     * @param  String $query  /$query c'est la requette ("SELECT * FROM ... WHERE ...")
     * @param  Array $params /$Param c'est les parametres de la requette ['id_key' => 'xxxx', ...]
     * @return Array        /Retourne les resultats de la requette sous forme de tableau assoc
     */
    public static function staticFetchAll(string $query, array $params = [])
    {
        $req = self::connexion()->prepare($query);
        $req->execute($params);
        $data = $req->fetchAll(PDO::FETCH_ASSOC);
        $req->closeCursor();
        return $data;
    }


    /**
     * queryGetFetch
     * Fais un fetch et retourne les resultats
     * @param  String          /$query c'est la requette ("SELECT * FROM ... WHERE ...")
     * @param  Array          /$Param c'est les parametres de la requette ['id_key' => 'xxxx', ...]
     * @return Array         / Retourne les resultats de la requette sous forme de tableau
     */
    public static function staticFetch(string $query, array $params = [])
    {
        $req = self::connexion()->prepare($query);
        $req->execute($params);
        $data = $req->fetch();
        $req->closeCursor();
        return $data;
    }


    /**
     * queryCount
     * Fais un count au niveau de la base de donnée
     * @param  String          /$query c'est la requette ("SELECT * FROM ... WHERE ...")
     * @param  Array          /$Param c'est les parametres de la requette ['id_key' => 'xxxx', ...]
     * @return Int           /Retourne le resultat de la requette
     */
    public static function staticCount(string $query, array $params = [])
    {
        $req = self::connexion()->prepare($query);
        $req->execute($params);
        $rcount = $req->rowCount();
        $req->closeCursor();
        return $rcount;
    }


    /**
     * queryModify
     * Modifie les données (INSERT, UPDATE, DELETE, ...)
     * @param  String          /$query c'est la requette ("SELECT * FROM ... WHERE ...")
     * @param  Array          /$Param c'est les parametres de la requette ['id_key' => 'xxxx', ...]
     * @return Boolean       /renvoi True si c'est bon et False si c'est pas bon
     */
    public static function staticModify(string $query, array $params = [])
    {
        $req = self::connexion()->prepare($query);
        $result = $req->execute($params);
        $req->closeCursor();
        return $result;
    }
}
