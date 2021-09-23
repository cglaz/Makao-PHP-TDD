<?php

namespace Makao\Service;

use Makao\Exception\CardNotFoundException;
use Makao\Exception\GameException;
use Makao\Logger\Logger;
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

    /** @var Logger */
    private $logger;

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
            $this->log('Player ' . $player . ' join to the game!');
            $this->table->addPlayer($player);
        }

        return $this;
    }

    public function startGame() : void
    {
        $this->log('Validate game before start!');
        $this->validateBeforeStartGame();
        $this->log('Game is OK -> Let\'s start');

        $cardDeck = $this->table->getCardDeck();
        try {
            $this->isStarted = true;

            $card = $this->cardService->pickFirstNoActionCard($cardDeck);
            $this->log('First played card is ' . $card);
            $this->table->addPlayedCard($card);

            foreach ($this->table->getPlayers() as $player) {
                $this->log('Player ' . $player . ' take cards:');
                $player->takeCards($cardDeck, self::COUNT_START_PLAYER_CARDS);
                $this->log('Player cards: ' . $player->getCards());
            }
        } catch (\Exception $e) {
            throw new GameException('The game needs help!', $e);
        }
    }

    public function prepareCardDeck() : Table
    {
        $this->log('Create Card Deck');
        $cardCollection = $this->cardService->createDeck();

        $this->log('Shuffle Card Deck');
        $cardDeck = $this->cardService->shuffle($cardCollection);

        $this->log('Add Card Deck to Table');
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
        $this->log('Rebuild card deck');
        $this->rebuildCardDeck();

        $table = $this->table;
        $player = $table->getCurrentPlayer();
        $this->log('Current player: ' . $player);
        if (!$player->canPlayRound()) {
            $this->log('Player ' . $player . ': Skip round');
            $table->finishRound();
            return;
        }

        try {
            $selectedCard = $this->cardSelector->chooseCard(
                $player,
                $table->getPlayedCards()->getLastCard(),
                $table->getPlayedCardColor()
            );

            $this->log('Player ' . $player . ': Play card ' . $selectedCard->getCard());
            $table->addPlayedCard($selectedCard->getCard());

            $this->cardActionService->afterCard($selectedCard->getCard(), $selectedCard->getRequest());
        } catch (CardNotFoundException $e) {
            $this->log('Player ' . $player . ': Take card');
            $player->takeCards($table->getCardDeck());
            $this->log('Player ' . $player . ': Finish round!');
            $table->finishRound();
        }
    }

    private function rebuildCardDeck() : void
    {
        try {
            $this->cardService->rebuildDeckFromPlayedCards(
                $this->table->getCardDeck(),
                $this->table->getPlayedCards()
            );
        } catch (CardNotFoundException $e) {
            throw new GameException('The game needs help!', $e);
        }
    }

    public function setLogger(Logger $logger) : self
    {
        $this->logger = $logger;

        return $this;
    }

    public function log(string $message) : void
    {
        if ($this->logger instanceof Logger) {
            $this->logger->log($message);
        }
    }
}