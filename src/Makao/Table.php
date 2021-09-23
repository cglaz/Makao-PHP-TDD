<?php

namespace Makao;

use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\TooManyPlayersAtTheTableException;

class Table
{
    const MAX_PLAYERS = 4;

    private $players = [];
    private $currentIndexPlayer = 0;

    /** @var CardCollection */
    private $cardDeck;

    /** @var CardCollection */
    private $playedCard;

    /** @var string */
    private $playedCardColor;

    public function __construct(CardCollection $cardDeck = null, CardCollection $playedCard = null)
    {
        $this->cardDeck = $cardDeck ?? new CardCollection();
        $this->playedCard = $playedCard ?? new CardCollection();

        if (!is_null($playedCard)) {
            $this->changePlayedCardColor($this->playedCard->getLastCard()->getColor());
        }
    }

    public function countPlayers()
    {
        return count($this->players);
    }

    public function addPlayer($player) : void
    {
        if ($this->countPlayers() == self::MAX_PLAYERS) {
            throw new TooManyPlayersAtTheTableException(self::MAX_PLAYERS);
        }
        $this->players[] = $player;
    }

    public function getPlayedCards() : CardCollection
    {
        return $this->playedCard;
    }

    public function getCardDeck() : CardCollection
    {
        return $this->cardDeck;
    }

    public function addCardCollectionToDeck(CardCollection $cardCollection) : self
    {
        $this->cardDeck->addCollection($cardCollection);

        return $this;
    }

    public function getCurrentPlayer() : Player
    {
        return $this->players[$this->currentIndexPlayer];
    }

    public function getNextPlayer() : Player
    {
        return $this->players[$this->currentIndexPlayer + 1] ?? $this->players[0];
    }

    public function getPreviousPlayer() : Player
    {
        return $this->players[$this->currentIndexPlayer - 1] ?? $this->players[$this->countPlayers() - 1];
    }

    public function finishRound() : void
    {
        if (++$this->currentIndexPlayer === $this->countPlayers()) {
            $this->currentIndexPlayer = 0;
        }
    }

    public function backRound() : void
    {
        if (--$this->currentIndexPlayer < 0) {
            $this->currentIndexPlayer = $this->countPlayers() - 1;
        }
    }

    public function getPlayedCardColor() : string
    {
        if (!is_null($this->playedCardColor)) {
            return $this->playedCardColor;
        }

        throw new CardNotFoundException('No played cards on the table yet!');
    }

    public function addPlayedCard(Card $card) : self
    {
        $this->playedCard->add($card);
        $this->changePlayedCardColor($card->getColor());

        return $this;
    }

    public function addPlayedCards(CardCollection $cards) : self
    {
        foreach ($cards as $card) {
            $this->addPlayedCard($card);
        }

        return $this;
    }

    public function changePlayedCardColor(string $color) : self
    {
        $this->playedCardColor = $color;

        return $this;
    }

    /**
     * @return Player[]
     */
    public function getPlayers() : array
    {
        return $this->players;
    }
}