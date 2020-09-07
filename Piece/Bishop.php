<?php

require_once 'AbstractPiece.php';

class Bishop extends AbstractPiece
{

    public function move($from, $to, &$pieces)
    {
        $dx = $to[0] > $from[0] ? 1 : -1;
        $dy = $to[1] > $from[1] ? 1 : -1;
        if (abs(ord($to[0]) - ord($from[0])) == abs($to[1] - $from[1])) {
            for ($y = $from[1] + $dy, $x = ord($from[0]) + $dx; $y != $to[1]; $y = $y + $dy, $x = $x + $dx) {
                if (isset($pieces[chr($x) . $y])) {
                    throw new IncorrectMoveException('Bishop cannot leap over other pieces.');
                }
            }
        } else {
            throw new IncorrectMoveException('Incorrect move for Bishop');
        }
    }
}