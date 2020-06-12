<?php
require_once 'Piece/Factory.php';
require_once 'Piece/Pieces.php';
require_once 'Exceptions/IncorrectMoveException.php';

class Game
{
    private $pieces;
    private int $playerNumber = 1;
    private int $gameNumber;
    private $database;
    private $factory;

    private function parseResponse($response, $color)
    {
        $result = explode(';', $response);
        foreach ($result as $item) {
            $item = explode('=', $item);
            $this->pieces[$item[0]] = $this->factory->getPiece($item[1], $color);
        }
        ksort($this->pieces);
    }

    public function __construct($database, $game)
    {
        $this->factory = new Factory();
        $this->database = $database;
        if ($game == null) {
            $this->database->query('INSERT INTO game_status VALUE ()');
            $this->gameNumber = $this->database->getIndex();
            $this->reset();
        } else {
            $playerNumber = $this->database->query('SELECT player_number FROM game_status WHERE id = ' . $game);
            $this->playerNumber = $playerNumber->fetch_object()->player_number;
            $playerNumber->close(); // TODO со всеми сделать также
            $result = $this->database->query('SELECT black, white FROM game_status WHERE id = ' . $game);
            $this->gameNumber = $game;
            while ($row = mysqli_fetch_array($result)) {
                $this->parseResponse($row['black'], 'Black');
                $this->parseResponse($row['white'], 'White');
            }
        }
    }

    public function reset()
    {
        $white = Pieces::getStartPositions();
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
        $this->database->query("UPDATE game_status SET black = ('" . $black . "'), white = ('" . $white . "') WHERE id = " . $this->gameNumber);
        $this->database->query('UPDATE game_status SET player_number = 1 WHERE id = ' . $this->gameNumber);
        $this->parseResponse($black, 'Black');
        $this->parseResponse($white, 'White');
    }

    private function checkCoordinates($coordinates)
    {
        if (strlen($coordinates) != 2) {
            throw new IncorrectMoveException('Length of coordinates must be 2. For example, e1 or A7.');
        }
        if ($coordinates[0] < 'a' or $coordinates[0] > 'h' or $coordinates[1] < '1' or $coordinates[1] > '8') {
            throw new IncorrectMoveException('Incorrect coordinates.');
        }
    }

    public function move($from, $to)
    {
        $from = strtolower($from);
        $to = strtolower($to);
        try {
            $this->checkCoordinates($from);
            $this->checkCoordinates($to);
        } catch (IncorrectMoveException $e) {
            throw $e;
        }
        if (!isset($this->pieces[$from])) {
            throw new IncorrectMoveException('There is no piece on the \'from\' coordinate.');
        }
        $colors = [
            1 => 'White',
            2 => 'Black'
        ];
        if ($to == $from) {
            throw new IncorrectMoveException('You cannot stay on the same position.');
        }
        if (isset($this->pieces[$from]) && $this->pieces[$from]->getColor() != $colors[$this->playerNumber]) {
            throw new IncorrectMoveException('You cannot move not your pieces.');
        }
        if (isset($this->pieces[$to]) and $this->pieces[$to]->getColor() == $this->pieces[$from]->getColor()) {
            throw new IncorrectMoveException('You cannot move on your pieces.');
        }
        try {
            $this->pieces[$from]->move($from, $to, $this->pieces);
            $currentPiece = $this->pieces[$from];
            unset($this->pieces[$from]);
            $this->pieces[$to] = $currentPiece;
            $this->updatePositions();
        } catch (IncorrectMoveException | DatabaseException $e) {
            throw $e;
        }
        $this->playerNumber = $this->playerNumber == 1 ? 2 : 1;
        $this->database->query('UPDATE game_status SET player_number = ' . $this->playerNumber . ' WHERE id = ' . $this->gameNumber);
        ksort($this->pieces);
    }

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
        $this->database->query("UPDATE game_status SET black = ('" . $black . "'), white = ('" . $white . "') WHERE id = " . $this->gameNumber);
    }

    public function getPlayerNumber()
    {
        return $this->playerNumber;
    }

    public function getPositions()
    {
        $positions = [];
        foreach ($this->pieces as $key => $value) {
            $positions[$key] = $value->getColor() . ' ' . $value->getName();
        }
        return $positions;
    }

    public function getGameNumber()
    {
        return $this->gameNumber;
    }

    private function findKing($color)
    {
        foreach ($this->pieces as $key => $value) {
            if ($value->getName() == 'King' && $value->getColor() == $color) {
                return $key;
            }
        }
        return '';
    }

    public function checkEndGame()
    {
        $flag = false;
        $colors = ['White', 'Black'];

        foreach ($colors as $value) {
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
                        if ($newPosition != $pieceKey and $this->canHit($newPosition, $pieceKey)) {
                            $matePositions[$newPosition] = $pieceKey;
                        } else {
                            try {
                                $this->checkCoordinates($newPosition);
                                if (!isset($this->pieces[$newPosition]) or $this->pieces[$newPosition]->getColor() != $value) {
                                    $safePositions[$newPosition] = 1;
                                }
                            } catch (IncorrectMoveException $ignore) {}
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

    private function canHit($position, $pieceKey)
    {
        try {
            $this->checkCoordinates($position);
            $this->pieces[$pieceKey]->move($pieceKey, $position, $this->pieces);
            return true;
        } catch (IncorrectMoveException $ignore) {}
        return false;
    }
}