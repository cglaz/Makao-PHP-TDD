<?php

namespace Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;

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

    private function isAction(Card $card) : bool
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

}