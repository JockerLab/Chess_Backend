<?php

require_once __DIR__ . '/../Properties/DatabaseCredentials.php';
require_once __DIR__ . '/../Exceptions/DatabaseException.php';

class Database
{
    /**
     * @var mixed current database
     */
    private $link;

    /**
     * Database constructor.
     */
    public function __construct()
    {
    }

    /**
     * Connecting to database
     *
     * @throws DatabaseException if cannot connect to database
     */
    public function connect()
    {
        $credentials = getDatabaseCredentials();
        $this->link = mysqli_connect($credentials['host'], $credentials['user'], $credentials['password'], $credentials['database']);
        if ($this->link == false) {
            throw new DatabaseException("Cannot connect to database. " . mysqli_connect_error());
        }
        mysqli_set_charset($this->link, 'utf8');
        mysqli_select_db($this->link, 'chess_data');
    }

    /**
     * Making query  in database
     *
     * @param $sql string query
     * @return bool|mysqli_result if query is done successfully
     * @throws DatabaseException if error occurred during making query
     */
    private function query($sql)
    {
        $result = mysqli_query($this->link, $sql);

        if ($result == false) {
            throw new DatabaseException("Error occurred during making query. " . mysqli_error($this->link));
        }

        return $result;
    }

    /**
     * Making update query in database
     *
     * @param $query string query
     * @param $game mixed number of current game
     * @throws DatabaseException if error occurred during making query
     */
    public function update($query, $game)
    {
        $this->query('UPDATE game_status SET ' . $query . ' WHERE id = ' . $game);
    }

    /**
     * Making insert query in database
     *
     * @throws DatabaseException if error occurred during making query
     */
    public function insert()
    {
        $this->query('INSERT INTO game_status VALUE ()');
    }

    /**
     * Making select query in database
     *
     * @param $query string query
     * @param $game mixed number of current game
     * @return bool|mysqli_result if query is done successfully
     * @throws DatabaseException if error occurred during making query
     */
    public function select($query, $game)
    {
        return $this->query('SELECT ' . $query . ' FROM game_status WHERE id = ' . $game);
    }

    /**
     * Disconnect from database
     */
    public function disconnect()
    {
        mysqli_close($this->link);
    }

    /**
     * Getting index from database
     *
     * @return int|string id of added note
     */
    public function getIndex()
    {
        return mysqli_insert_id($this->link);
    }
}