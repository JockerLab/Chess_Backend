<?php

require_once 'Exceptions/IncorrectMoveException.php';
interface Piece
{
    public function getColor();
    public function getName();
    public function move($from, $to, $pieces);
}