<?php

require_once __DIR__ . '/../Properties/DatabaseCredentials.php';
require_once __DIR__ . '/../Exceptions/DatabaseException.php';

class Database
{
    private $link;

    public function __construct() {}

    public function connect() {
        $credentials = getDatabaseCredentials();
        $this->link = mysqli_connect($credentials['host'], $credentials['user'], $credentials['password'], $credentials['database']);
        if ($this->link == false) {
            throw new DatabaseException("Cannot connect to database. " . mysqli_connect_error());
        }
        mysqli_set_charset($this->link, 'utf8');
        mysqli_select_db($this->link, 'chess_data');
    }
    public function query($sql) {
        $result = mysqli_query($this->link, $sql);

        if ($result == false) {
            throw new DatabaseException("Error occurred during making query. " . mysqli_error($this->link));
        }

        return $result;
    }
    public function disconnect() {
        mysqli_close($this->link);
    }
    public function getIndex() {
        return mysqli_insert_id($this->link);
    }
}