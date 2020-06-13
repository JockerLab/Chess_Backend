<?php

require_once __DIR__ . '/../Exceptions/IncorrectMoveException.php';

interface Piece
{
    /**
     * @return string color of current {@link Piece}
     */
    public function getColor();

    /**
     * @return string name of current {@link Piece}
     */
    public function getName();

    /**
     * @param $from string coordinate of begin
     * @param $to string coordinate of end
     * @param $pieces array map from coordinates into {@link Piece}
     * @return void if move is correct
     * @throws IncorrectMoveException if move is incorrect
     */
    public function move($from, $to, $pieces);
}