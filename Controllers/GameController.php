<?php

require_once 'SessionController.php';
require_once 'ApiController.php';
require_once __DIR__ . '/../Exceptions/DatabaseException.php';
require_once __DIR__ . '/../Exceptions/IncorrectMoveException.php';
require_once __DIR__ . '/../Game/Game.php';

class GameController
{
    /**
     * Make move in current game
     *
     * @param $currentGame mixed instance of {@link Game}
     * @param $coordinates mixed coordinates of {@link Piece}
     */
    public static function makeMove($currentGame, $coordinates)
    {
        try {
            if (SessionController::checkEndState()) {
                $currentGame->move($coordinates[0], $coordinates[1]);
                if ($currentGame->checkEndGame()) {
                    SessionController::changeEndState(true);
                    ApiController::sendResponse('OK', 'The game is over.');
                }
                ApiController::sendResponse('OK', 'Piece was successfully moved.');
            } else {
                ApiController::sendResponse('OK', 'The game is already over.');
            }
        } catch (IncorrectMoveException | DatabaseException $e) {
            ApiController::sendResponse('ERROR', $e->getMessage());
        }
    }

    /**
     * Check coordinates
     *
     * @param $coordinates mixed coordinates to check
     * @throws IncorrectMoveException if coordinates are incorrect
     */
    public static function checkCoordinates($coordinates)
    {
        if (strlen($coordinates) != 2) {
            throw new IncorrectMoveException('Length of coordinates must be 2. For example, e1 or A7.');
        }
        if ($coordinates[0] < 'a' or $coordinates[0] > 'h' or $coordinates[1] < '1' or $coordinates[1] > '8') {
            throw new IncorrectMoveException('Incorrect coordinates.');
        }
    }

    /**
     * Return start positions of white {@link Piece}
     *
     * @return string start positions of white {@link Piece}
     */
    public static function getStartPositions()
    {
        return "a1=Rook;e1=King;h1=Rook";
        /*return "a1=Rook;b1=Knight;c1=Bishop;d1=Queen;e1=King;f1=Bishop;g1=Knight;h1=Rook;" .
            "a2=Pawn;b2=Pawn;c2=Pawn;d2=Pawn;e2=Pawn;f2=Pawn;g2=Pawn;h2=Pawn";*/
    }
}