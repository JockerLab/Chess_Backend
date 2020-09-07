<?php

require_once 'AbstractPiece.php';

class Rook extends AbstractPiece
{
    private bool $firstMove = true;

    public function move($from, $to, &$pieces)
    {
        $dx = $to[0] > $from[0] ? 1 : -1;
        $dy = $to[1] > $from[1] ? 1 : -1;
        if (abs(ord($from[0]) - ord($to[0])) == 0 or abs($from[1] - $to[1]) == 0) {
            for ($y = $from[1] + $dy, $x = ord($from[0]) + $dx; $y != $to[1]; $y = $y + $dy, $x = $x + $dx) {
                if (isset($pieces[chr($x) . $y])) {
                    throw new IncorrectMoveException('Rook cannot leap over other pieces.');
                }
            }
            return;
        } else {
            throw new IncorrectMoveException('Incorrect move for Rook');
        }
    }

    /**
     * @return bool true if this {@link Piece} move
     *              false else
     */
    public function isFirstMove(): bool
    {
        return $this->firstMove;
    }

    /**
     * @param bool $firstMove
     */
    public function setFirstMove(bool $firstMove): void
    {
        $this->firstMove = $firstMove;
    }
}