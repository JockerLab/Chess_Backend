<?php

require_once __DIR__ . '/../Game/Game.php';
require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Exceptions/DatabaseException.php';
require_once __DIR__ . '/../Exceptions/IncorrectMoveException.php';

class SessionController
{
    /**
     * Put database in session if it absent
     */
    public static function putDatabaseIfAbsent() {
        if (!isset($_SESSION['database'])) {
            $_SESSION['database'] = new Database();
        }
    }

    /**
     * Connect to database
     *
     * @return bool|DatabaseException|Exception Exception if error occurred during connecting
     */
    public static function connectDatabase() {
        try {
            $_SESSION['database']->connect();
        } catch (DatabaseException $e) {
            return $e;
        }
        return false;
    }

    /**
     * Disconnect from database
     */
    public static function disconnectDatabase() {
        $_SESSION['database']->disconnect();
    }

    /**
     * Get instance of {@link Game} in session or create new
     *
     * @return Game instance of {@link Game} in session or new
     */
    public static function getSessionGame() {
        $currentGame = null;
        if (isset($_SESSION['game'])) {
            $currentGame = new Game($_SESSION['database'], $_SESSION['game']);
        } else {
            $currentGame = new Game($_SESSION['database'], null);
            $_SESSION['game'] = $currentGame->getGameNumber();
        }
        return $currentGame;
    }

    /**
     * Check if game is already over
     *
     * @return bool true if game is already over
     */
    public static function checkEndState() {
        return (!isset($_SESSION['end']) or $_SESSION['end'] == false);
    }

    /**
     * Change end state in session
     *
     * @param $state bool true if game is over
     */
    public static function changeEndState($state) {
        $_SESSION['end'] = $state;
    }
}