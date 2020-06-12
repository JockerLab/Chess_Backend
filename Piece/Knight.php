<?php

require_once 'AbstractPiece.php';

class Knight extends AbstractPiece
{

    public function move($from, $to, $pieces)
    {
        if ((abs(ord($from[0]) - ord($to[0])) == 1 and abs($from[1] - $to[1]) == 2) or
            (abs(ord($from[0]) - ord($to[0])) == 2 and abs($from[1] - $to[1]) == 1)) {
            return;
        } else {
            throw new IncorrectMoveException('Incorrect move for Knight');
        }
    }
}