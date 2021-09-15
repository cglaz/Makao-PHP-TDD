<?php

namespace Makao\Service;

use Makao\Exception\CardNotFoundException;
use Makao\Exception\GameException;
use Makao\Service\CardSelector\CardSelectorInterface;
use Makao\Table;

class GameService
{
    const MINIMAL_PLAYERS = 2;
    const COUNT_START_PLAYER_CARDS = 5;

    /** @var Table */
    private $table;

    /** @var bool */
    private $isStarted = false;

    /** @var CardService */
    private $cardService;

    /** @var CardSelectorInterface */
    private $cardSelector;

    /** @var CardActionService */
    private $cardActionService;

    public function __construct(
        Table $table,
        CardService $cardService,
        CardSelectorInterface $cardSelector,
        CardActionService $cardActionService
    ) {
        $this->table = $table;
        $this->cardService = $cardService;
        $this->cardSelector = $cardSelector;
        $this->cardActionService = $cardActionService;
    }

    public function isStarted() : bool
    {
        return $this->isStarted;
    }

    public function getTable() : Table
    {
        return $this->table;
    }

    public function addPlayers(array $players) : self
    {
        foreach ($players as $player) {
            $this->table->addPlayer($player);
        }

        return $this;
    }

    public function startGame() : void
    {
        $this->validateBeforeStartGame();

        $cardDeck = $this->table->getCardDeck();
        try {
            $this->isStarted = true;

            $card = $this->cardService->pickFirstNoActionCard($cardDeck);
            $this->table->addPlayedCard($card);

            foreach ($this->table->getPlayers() as $player) {
                $player->takeCards($cardDeck, self::COUNT_START_PLAYER_CARDS);
            }
        } catch (\Exception $e) {
            throw new GameException('The game needs help!', $e);
        }
    }

    public function prepareCardDeck() : Table
    {
        $cardCollection = $this->cardService->createDeck();
        $cardDeck = $this->cardService->shuffle($cardCollection);

        return $this->table->addCardCollectionToDeck($cardDeck);
    }

    private function validateBeforeStartGame() : void
    {
        if (0 === $this->table->getCardDeck()->count()) {
            throw new GameException('Prepare card deck before game start');
        }

        if (self::MINIMAL_PLAYERS > $this->table->countPlayers()) {
            throw new GameException('You need minimum ' . self::MINIMAL_PLAYERS . ' players to start game');
        }
    }

    public function playRound() : void
    {
        $player = $this->table->getCurrentPlayer();
        if (!$player->canPlayRound()) {
            $this->table->finishRound();
            return;
        }

        try {
            $selectedCard = $this->cardSelector->chooseCard(
                $player,
                $this->table->getPlayedCards()->getLastCard(),
                $this->table->getPlayedCardColor()
            );

            $this->table->addPlayedCard($selectedCard->getCard());

            $this->cardActionService->afterCard($selectedCard->getCard(), $selectedCard->getRequest());
        } catch (CardNotFoundException $e) {
            $player->takeCards($this->table->getCardDeck());
            $this->table->finishRound();
        }
    }
}