<?php

namespace Makao;

use Makao\Collection\CardCollection;

class Player
{
    const MAKAO = 'Makao';
    /**
     * @var string
     */
    private $name;

    /**
     * @var CardCollection
     */
    private $cardCollection;

    public function __construct(string $name, CardCollection $cardCollection = null)
    {
        $this->name = $name;
        $this->cardCollection = $cardCollection ?? new CardCollection();
    }

    public function __toString() : string
    {
        return $this->name;
    }

    public function getCards() : CardCollection
    {
        return $this->cardCollection;
    }

    public function pickCard() : Card
    {
        return $this->getCards()->pickCard();
    }

    public function takeCards(CardCollection $cardCollection, int $count = 1) : self
    {
        for($i=0; $i<$count; $i++) {
            $this->cardCollection->add($cardCollection->pickCard());
        }

        return $this;
    }

    public function sayMakao() : string
    {
        return  self::MAKAO;
    }

}