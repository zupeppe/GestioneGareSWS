<?php
/**
 * Classe Database
 * 
 * Implementa il pattern Singleton per gestire la connessione al database MySQL tramite PDO.
 */
class Database {
    /**
     * @var Database|null Istanza unica della classe
     */
    private static $istanza = null;

    /**
     * @var PDO Oggetto di connessione PDO
     */
    private $connessione;

    /**
     * @var string Host del database
     */
    private $host = '127.0.0.1';

    /**
     * @var string Nome del database
     */
    private $nome_db = 'gestione_gare_sws';

    /**
     * @var string Nome utente database
     */
    private $username = 'root';

    /**
     * @var string Password database
     */
    private $password = '';

    /**
     * Costruttore privato.
     * Stabilisce la connessione PDO al database.
     */
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->nome_db};charset=utf8mb4";
            $opzioni = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->connessione = new PDO($dsn, $this->username, $this->password, $opzioni);
        } catch (PDOException $e) {
            die("Errore di connessione al database: " . $e->getMessage());
        }
    }

    /**
     * Restituisce l'istanza unica della classe Database.
     * 
     * @return Database L'istanza Singleton
     */
    public static function getIstanza() {
        if (self::$istanza === null) {
            self::$istanza = new Database();
        }
        return self::$istanza;
    }

    /**
     * Restituisce l'oggetto PDO per eseguire query.
     * 
     * @return PDO L'oggetto di connessione
     */
    public function getConnessione() {
        return $this->connessione;
    }

    /**
     * Impedisce la clonazione dell'oggetto.
     */
    private function __clone() {}

    /**
     * Impedisce la deserializzazione dell'oggetto.
     */
    public function __wakeup() {}
}
