<?php

require_once 'Piece.php';
require_once __DIR__ . '/../Exceptions/IncorrectMoveException.php';

abstract class AbstractPiece implements Piece
{
    private $name, $color;

    public function __construct($name, $color)
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getName()
    {
        return $this->name;
    }

    abstract public function move($from, $to, &$pieces);
}