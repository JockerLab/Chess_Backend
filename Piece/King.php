<?php

require_once 'AbstractPiece.php';

class King extends AbstractPiece
{
    private bool $firstMove = true;

    public function move($from, $to, &$pieces)
    {
        if (abs(ord($from[0]) - ord($to[0])) <= 1 and abs($from[1] - $to[1]) <= 1) {
            $this->firstMove = false;
            return;
        } else {
            if (abs(ord($from[0]) - ord($to[0])) == 2 and
                abs($from[1] - $to[1]) == 0 and
                $this->firstMove == true) {
                $dx = $from[0] > $to[0] ? -1 : 1;
                $last = $from[0] > $to[0] ? 'a' : 'h';
                $x = ord($from[0]) + $dx;
                for ($y = $from[1]; chr($x) != $last; $x = $x + $dx) {
                    if (isset($pieces[chr($x) . $y])) {
                        throw new IncorrectMoveException('Cannot castling, there are extra pieces');
                    }
                }

                if (isset($pieces[$last . $from[1]]) and
                    $pieces[$last . $from[1]] instanceof Rook and
                    $pieces[$last . $from[1]]->isFirstMove() == true) {
                    return;
                }
            }
            throw new IncorrectMoveException('Incorrect move for King');
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