<?php

namespace Makao;

class Card
{
    const VALUE_TWO = '2';
    const VALUE_THREE = '3';
    const VALUE_FOUR = '4';
    const VALUE_FIVE = '5';
    const VALUE_SIX = '6';
    const VALUE_SEVEN = '7';
    const VALUE_EIGHT = '8';
    const VALUE_NINE = '9';
    const VALUE_TEN = '10';
    const VALUE_JACK = 'jack';
    const VALUE_QUEEN = 'queen';
    const VALUE_KING = 'king';
    const VALUE_ACE = 'ace';

    const COLOR_DIAMOND = 'diamond';
    const COLOR_SPADE = 'spade';
    const COLOR_CLUB = 'club';
    const COLOR_HEART = 'heart';

    private $color;
    private $value;

    public function __construct(string $color, string $value)
    {
        $this->color = $color;
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public static function values() : array
    {
        return [
             self::VALUE_TWO,
             self::VALUE_THREE,
             self::VALUE_FOUR,
             self::VALUE_FIVE,
             self::VALUE_SIX,
             self::VALUE_SEVEN,
             self::VALUE_EIGHT,
             self::VALUE_NINE,
             self::VALUE_TEN,
             self::VALUE_JACK,
             self::VALUE_QUEEN,
             self::VALUE_KING,
             self::VALUE_ACE,
        ];
    }

    public static function colors() : array
    {
        return [
            self::COLOR_DIAMOND,
            self::COLOR_SPADE,
            self::COLOR_CLUB,
            self::COLOR_HEART,
        ];
    }
}