<?php

require_once __DIR__ . '/../Piece/Factory.php';
require_once __DIR__ . '/../Controllers/GameController.php';
require_once __DIR__ . '/../Exceptions/IncorrectMoveException.php';
require_once __DIR__ . '/../Exceptions/DatabaseException.php';
require_once __DIR__ . '/../Database/Database.php';

class Game
{
    /**
     * @var mixed map from coordinates into {@link Piece}
     */
    private $pieces;
    /**
     * @var int number of player
     */
    private int $playerNumber = 1;
    /**
     * @var int number of current game
     */
    private int $gameNumber;
    /**
     * @var mixed instance of current {@link Database}
     */
    private $database;
    /**
     * @var mixed instance of current {@link Factory}
     */
    private $factory;
    /**
     * @var string[] map from player number into color
     */
    private $colors = [
        1 => 'White',
        2 => 'Black'
    ];

    /**
     * Game constructor.
     *
     * @param $database mixed instance of current {@link Database}
     * @param $game mixed number of current game
     */
    public function __construct($database, $game)
    {
        $this->factory = new Factory();
        $this->database = $database;
        try {
            if ($game == null) {
                $this->database->insert();
                $this->gameNumber = $this->database->getIndex();
                $this->reset();
            } else {
                $playerNumber = $this->database->select('player_number', $game);
                $this->playerNumber = $playerNumber->fetch_object()->player_number;
                $playerNumber->close();
                $result = $this->database->select('black, white', $game);
                $this->gameNumber = $game;
                while ($row = mysqli_fetch_array($result)) {
                    $this->parseResponse($row['black'], 'Black');
                    $this->parseResponse($row['white'], 'White');
                }
                $result->close();
            }
        } catch (DatabaseException $e) {
            return null;
        }
    }

    /**
     * Split string and extract coordinates with pieces
     *
     * @param $response string string to parse
     * @param $color string color of current pieces
     */
    private function parseResponse($response, $color)
    {
        $result = explode(';', $response);
        foreach ($result as $item) {
            $item = explode('=', $item);
            $this->pieces[$item[0]] = $this->factory->getPiece($item[1], $color);
        }
        ksort($this->pieces);
    }

    /**
     * Reset start state of the game
     *
     * @return bool true if game successfully reseted
     */
    public function reset()
    {
        if (isset($this->pieces)) {
            unset($this->pieces);
        }
        $white = GameController::getStartPositions();
        $black = "";
        for ($i = 0; $i < strlen($white); $i++) {
            if ($white[$i] == '1') {
                $black = $black . '8';
            } elseif ($white[$i] == '2') {
                $black = $black . '7';
            } else {
                $black = $black . $white[$i];
            }
        }
        $this->parseResponse($black, 'Black');
        $this->parseResponse($white, 'White');
        try {
            $this->updatePositions();
            $this->updatePlayerNumber(1);
        } catch (DatabaseException $e) {
            return false;
        }
        return true;
    }

    /**
     * Move {@link Piece} by input coordinates
     *
     * @param $from string coordinate of begin
     * @param $to string coordinate of end
     * @throws DatabaseException if error occurred during making request to database
     * @throws IncorrectMoveException if move is incorrect
     */
    public function move($from, $to)
    {
        $from = strtolower($from);
        $to = strtolower($to);
        try {
            GameController::checkCoordinates($from);
            GameController::checkCoordinates($to);
        } catch (IncorrectMoveException $e) {
            throw $e;
        }
        if (!isset($this->pieces[$from])) {
            throw new IncorrectMoveException('There is no piece on the \'from\' coordinate.');
        }
        if ($to == $from) {
            throw new IncorrectMoveException('You cannot stay on the same position.');
        }
        if (isset($this->pieces[$from]) and $this->pieces[$from]->getColor() != $this->colors[$this->playerNumber]) {
            throw new IncorrectMoveException('You cannot move not your pieces.');
        }
        if (isset($this->pieces[$to]) and $this->pieces[$to]->getColor() == $this->pieces[$from]->getColor()) {
            throw new IncorrectMoveException('You cannot move on your pieces.');
        }
        try {
            $this->pieces[$from]->move($from, $to, $this->pieces);
        } catch (IncorrectMoveException | DatabaseException $e) {
            throw $e;
        }
        if ($this->checkCastling($from, $to)) {
            $last = $from[0] > $to[0] ? 'a' : 'h';
            $dx = $from[0] > $to[0] ? -1 : 1;
            $rook = $last . $from[1];
            $this->pieces[$from]->setFirstMove(false);
            $this->pieces[$rook]->setFirstMove(false);
            $currentPiece1 = $this->pieces[$rook];
            unset($this->pieces[$rook]);
            $this->pieces[chr(ord($to[0]) - $dx) . $to[1]] = $currentPiece1;
        }
        $currentPiece = $this->pieces[$from];
        unset($this->pieces[$from]);
        $this->pieces[$to] = $currentPiece;
        $this->updatePositions();
        $this->playerNumber = $this->playerNumber == 1 ? 2 : 1;
        $this->updatePlayerNumber($this->playerNumber);
        ksort($this->pieces);
    }

    /**
     * Check if move is castling
     */
    private function checkCastling($from, $to) {
        if (abs(ord($from[0]) - ord($to[0])) == 2 and
            abs($from[1] - $to[1]) == 0 and
            $this->pieces[$from] instanceof King) {
            return true;
        }
        return false;
    }

    /**
     * Update positions in database
     */
    private function updatePositions()
    {
        $black = '';
        $white = '';
        foreach ($this->pieces as $key => $value) {
            if ($value->getColor() == 'White') {
                if ($white != '') {
                    $white = $white . ';';
                }
                $white = $white . $key . '=' . $value->getName();
            } else {
                if ($black != '') {
                    $black = $black . ';';
                }
                $black = $black . $key . '=' . $value->getName();
            }
        }
        $this->database->update("black = ('" . $black . "'), white = ('" . $white . "')", $this->gameNumber);
    }

    /**
     * Update number of player in database
     *
     * @param $value mixed current number of player
     */
    private function updatePlayerNumber($value)
    {
        $this->database->update('player_number = ' . $value, $this->gameNumber);
    }

    /**
     * Get number of current player
     *
     * @return int number of current player
     */
    public function getPlayerNumber()
    {
        return $this->playerNumber;
    }

    /**
     * Get current state of positions
     *
     * @return array state of positions
     */
    public function getState()
    {
        $positions = [];
        foreach ($this->pieces as $key => $value) {
            $positions[$key] = $value->getColor() . ' ' . $value->getName();
        }
        return $positions;
    }

    /**
     * Get number of current game
     *
     * @return int number of current game
     */
    public function getGameNumber()
    {
        return $this->gameNumber;
    }

    /**
     * Get position of {@link King} with input color
     *
     * @param $color string color of {@link King}
     * @return int|string position of {@link King} with input color
     */
    private function findKing($color)
    {
        foreach ($this->pieces as $key => $value) {
            if ($value->getName() == 'King' && $value->getColor() == $color) {
                return $key;
            }
        }
        return '';
    }

    /**
     * Check if game is over
     *
     * @return bool true if game is over
     */
    public function checkEndGame()
    {
        $flag = false;

        foreach ($this->colors as $key => $value) {
            $position = $this->findKing($value);
            $matePositions = [];
            $safePositions = [];
            foreach ($this->pieces as $pieceKey => $pieceValue) {
                if ($pieceKey == $position or $pieceValue->getColor() == $value) {
                    continue;
                }
                $d = [-1, 0, 1];
                for ($i = 0; $i < 3; $i++) {
                    for ($j = 0; $j < 3; $j++) {
                        $newPosition = chr(ord($position[0]) + $d[$i]) . ($position[1] + $d[$j]);
                        if ($newPosition != $pieceKey and $this->canHit($newPosition, $pieceKey, $position)) {
                            $matePositions[$newPosition] = $pieceKey;
                        } else {
                            try {
                                GameController::checkCoordinates($newPosition);
                                if (!isset($this->pieces[$newPosition]) or $this->pieces[$newPosition]->getColor() != $value) {
                                    $safePositions[$newPosition] = 1;
                                }
                            } catch (IncorrectMoveException $ignore) {
                            }
                        }
                    }
                }
            }
            if (!isset($matePositions[$position]) or count($matePositions) == 0) {
                continue;
            }
            $safePositions = array_diff_key($safePositions, $matePositions);
            if (isset($safePositions) and count($safePositions) > 0) {
                continue;
            }
            if (count($matePositions) > 1) {
                $flag = true;
                break;
            }
            $attackPosition = '';
            $whereAttack = '';
            foreach ($matePositions as $pieceKey => $pieceValue) {
                $whereAttack = $pieceKey;
                $attackPosition = $pieceValue;
            }
            $count = 0;
            $countSave = 0;
            foreach ($this->pieces as $pieceKey => $pieceValue) {
                if ($pieceKey == $position or $pieceValue->getColor() != $value) {
                    continue;
                }
                if ($this->canHit($attackPosition, $pieceKey)) {
                    $count++;
                    break;
                }
                if ($this->canHit($whereAttack, $pieceKey)) {
                    $countSave++;
                    break;
                }
            }
            if ($count == 0 or $countSave == 0) {
                $flag = true;
                break;
            }
        }

        return $flag;
    }

    /**
     * Check if {@link Piece} can hit input position
     *
     * @param $position string position which {@link Piece} should hit
     * @param $pieceKey string position of {@link Piece}
     * @param string $king position of the King
     * @return bool true if {@link Piece} can hit input position
     */
    private function canHit($position, $pieceKey, $king = '')
    {
        try {
            GameController::checkCoordinates($position);
            $copyPieces = $this->pieces;
            if ($king != '') {
                $kingPiece = $copyPieces[$king];
                unset($copyPieces[$king]);
                $copyPieces[$position] = $kingPiece;
            }
            $this->pieces[$pieceKey]->move($pieceKey, $position, $copyPieces);
            return true;
        } catch (IncorrectMoveException $ignore) {
        }
        return false;
    }
}