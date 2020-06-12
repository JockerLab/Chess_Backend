<?php

require_once 'AbstractPiece.php';

class Queen extends AbstractPiece
{

    public function move($from, $to, $pieces)
    {
        $dx = $to[0] >= $from[0] ? ($to[0] > $from[0] ? 1 : 0) : -1;
        $dy = $to[1] >= $from[1] ? ($to[1] > $from[1] ? 1 : 0) : -1;
        if (abs(ord($from[0]) - ord($to[0])) == 0 or abs($from[1] - $to[1]) == 0 or
            abs(ord($to[0]) - ord($from[0])) == abs($to[1] - $from[1])) {
            for ($y = $from[1] + $dy, $x = ord($from[0]) + $dx; $y != $to[1]; $y = $y + $dy, $x = $x + $dx) {
                if (isset($pieces[chr($x) . $y])) {
                    throw new IncorrectMoveException('Queen cannot leap over other pieces.');
                }
            }
        } else {
            throw new IncorrectMoveException('Incorrect move for Queen');
        }
    }
}