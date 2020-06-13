<?php
require_once 'King.php';
require_once 'Bishop.php';
require_once 'Knight.php';
require_once 'Pawn.php';
require_once 'Queen.php';
require_once 'Rook.php';

class Factory
{
    /**
     * Get instance of chess piece by name
     * @param $name string name of {@link Piece}
     * @param $color string color of {@link Piece}
     * @return Bishop|King|Knight|Pawn|Queen|Rook|string instance of chess piece
     */
    public function getPiece($name, $color)
    {
        switch ($name) {
            case 'King':
                return new King($name, $color);
                break;
            case 'Bishop':
                return new Bishop($name, $color);
                break;
            case 'Knight':
                return new Knight($name, $color);
                break;
            case 'Pawn':
                return new Pawn($name, $color);
                break;
            case 'Queen':
                return new Queen($name, $color);
                break;
            case 'Rook':
                return new Rook($name, $color);
                break;
        }
        return '';
    }
}