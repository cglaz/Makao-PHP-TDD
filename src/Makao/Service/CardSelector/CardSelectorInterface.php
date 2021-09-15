<?php

namespace Makao\Service\CardSelector;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Player;
use Makao\SelectedCard;

interface CardSelectorInterface
{
    /**
     * Choose card from player hand to play in his round.
     * When he hasn't card to play throw CardNotFoundException.
     * Return SelectedCard with chosen card and request for card action.
     *
     * @param Player $player
     * @param Card   $playedCard
     * @param string $acceptColor
     *
     * @return SelectedCard
     *
     * @throws CardNotFoundException
     */
    public function chooseCard(Player $player, Card $playedCard, string $acceptColor) : SelectedCard;
}