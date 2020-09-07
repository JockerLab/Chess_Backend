<?php

require_once 'AbstractPiece.php';

class Pawn extends AbstractPiece
{

    public function move($from, $to, &$pieces)
    {
        $current = $pieces[$from];
        $start = $current->getColor() == 'White' ? 2 : 7;
        $multiplier = $current->getColor() == 'White' ? 1 : -1;
        if ($from[0] == $to[0]) {
            $between = $from[0] . ($from[1] + $multiplier);
            if ($to[1] - $from[1] == $multiplier * 2 and $from[1] == $start and !isset($pieces[$between])) {
                return;
            } elseif ($to[1] - $from[1] == $multiplier) {
                return;
            } else {
                throw new IncorrectMoveException('Incorrect move for Pawn');
            }
        } elseif (abs(ord($to[0]) - ord($from[0])) == 1) {
            if ($to[1] - $from[1] == $multiplier and isset($pieces[$to]) and $pieces[$to]->getColor() != $pieces[$from]->getColor()) {
                return;
            } else {
                throw new IncorrectMoveException('Incorrect move for Pawn');
            }
        } else {
            throw new IncorrectMoveException('Incorrect move for Pawn');
        }
    }
}