<?php

namespace Makao\Service\CardSelector;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Player;

interface CardSelectorInterface
{
    /**
     * Choose card from player hand to play in his round.
     * When he hasn't card to play throw CardNotFoundException.
     *
     * @param Player $player
     * @param Card $playedCard
     * @param string $acceptColor
     *
     * @return Card
     *
     * @throws CardNotFoundException
     */
    public function chooseCard(Player $player, Card $playedCard, string $acceptColor) : Card;
}