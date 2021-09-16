<?php

namespace Makao\Service;

use http\Encoding\Stream\Inflate;
use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Player;

class CardService
{
    /**
     * @var ShuffleService
     */
    private $shuffleService;

    public function __construct(ShuffleService $shuffleService)
    {
        $this->shuffleService = $shuffleService;
    }

    public function createDeck() : CardCollection
    {
        $deck = new CardCollection();

        foreach (Card::values() as $value) {
            foreach (Card::colors() as $color) {
                $deck->add(new Card($color, $value));
            }
        }

        return $deck;
    }

    public function shuffle(CardCollection $cardCollection) : CardCollection
    {

        return new CardCollection(
            $this->shuffleService->shuffle($cardCollection->toArray())
        );
    }

    public function pickFirstNoActionCard(CardCollection $collection) : Card
    {
        $firstCard = null;
        $card = $collection->pickCard();

        while($this->isAction($card) && $firstCard !== $card) {
            $collection->add($card);

            if (is_null($firstCard)) {
                $firstCard = $card;
            }

            $card = $collection->pickCard();
        }

        if ($this->isAction($card)) {
        throw new CardNotFoundException('No regular cards in colection');
        }

        return $card;
    }

    public function isAction(Card $card) : bool
    {
        return in_array($card->getValue(), [
            Card::VALUE_TWO,
            Card::VALUE_THREE,
            Card::VALUE_FOUR,
            Card::VALUE_JACK,
            Card::VALUE_QUEEN,
            Card::VALUE_KING,
            Card::VALUE_ACE,
        ]);
    }

    public function isRequestNeeded(Card $card) : bool
    {
        return in_array($card->getValue(), [
            Card::VALUE_JACK,
            Card::VALUE_ACE,
        ]);
    }

    public function getTheMostOccurringNoActionPlayerCardsValue(Player $player) : ?string
    {
        $cards = [];
        foreach ($player->getCards() as $card) {
            if (!$this->isAction($card)) {
                $cards[$card->getValue()] = isset($cards[$card->getValue()]) ? $cards[$card->getValue()] + 1 : 1;
            }
        }

        if (empty($cards)) {
            throw new CardNotFoundException('Player has no action cards!');
        }

        return array_keys($cards, max($cards))[0];
    }

    public function getTheMostOccurringPlayerCardsColor(Player $player) : ?string
    {
        $cards = [];
        foreach ($player->getCards() as $card) {
            $cards[$card->getColor()] = isset($cards[$card->getColor()]) ? $cards[$card->getColor()] + 1 : 1;
        }

        if (empty($cards)) {
            throw new CardNotFoundException('Player has no cards!');
        }

        return array_keys($cards, max($cards))[0];
    }

    public function rebuildDeckFromPlayedCards(CardCollection $deck, CardCollection $playedCards) : void
    {
        if (0 === $playedCards->count()) {
            throw new CardNotFoundException('Played cards collection is empty. You can not rebuild deck!');
        }

        $cards = new CardCollection();

        while (1 < $playedCards->count()) {
            $cards->add($playedCards->pickCard());
        }

        $deck->addCollection($this->shuffle($cards));
    }

}