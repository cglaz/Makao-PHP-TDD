<?php

namespace Makao\Validator;

use Makao\Card;
use Makao\Exception\CardDuplicationException;

class CardValidator
{
    /**
     * @param Card $activeCard
     * @param Card $newCard
     * @param string $acceptColor
     *
     * @return bool
     *
     * @throws CardDuplicationException
     */
    public function valid(Card $activeCard, Card $newCard, string $acceptColor) : bool
    {
        if ($activeCard === $newCard) {
            throw new CardDuplicationException($newCard);
        }

        if ($activeCard->getColor() !== $acceptColor) {
            return $acceptColor === $newCard->getColor();
        }

        return $activeCard->getColor() === $newCard->getColor()
            || $activeCard->getValue() === $newCard->getValue()
            || $newCard->getValue() === Card::VALUE_QUEEN
            || $activeCard->getValue() == Card::VALUE_QUEEN;
    }
}