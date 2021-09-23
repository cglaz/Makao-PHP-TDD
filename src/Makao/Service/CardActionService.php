<?php

namespace Makao\Service;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Logger\Logger;
use Makao\Table;

class CardActionService
{
    /** @var Table */
    private $table;

    /** @var int */
    private $actionCount = 0;

    /** @var Logger */
    private $logger;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function afterCard(Card $card, ?string $request = null) : void
    {
        $this->actionCount = 0;
        $this->table->finishRound();

        switch ($card->getValue()) {
            case Card::VALUE_TWO:
                $this->takingCards(Card::VALUE_TWO, 2);
                break;
            case Card::VALUE_THREE:
                $this->takingCards(Card::VALUE_THREE, 3);
                break;
            case Card::VALUE_FOUR:
                $this->skipRound();
                break;
            case Card::VALUE_JACK:
                $this->requestingCardValue($request);
                break;
            case Card::VALUE_KING:
                $this->afterKing($card->getColor());
                break;
            case Card::VALUE_ACE:
                $this->changePlayedColorCardOnTable($request);
                break;
            default:
                break;
        }
    }

    private function takingCards(string $cardValue, int $cardsToGet) : void
    {
        $this->actionCount += $cardsToGet;
        $player = $this->table->getCurrentPlayer();
        try {
            $card = $player->pickCardByValue($cardValue);
            $this->log('Player ' . $player . ': Defend with card ' . $card);
            $this->table->addPlayedCard($card);
            $this->log('Player ' . $player . ': Finish round');
            $this->table->finishRound();
            $this->takingCards($cardValue, $cardsToGet);
        } catch (CardNotFoundException $e) {
            $this->playerTakeCards($this->actionCount);
        }
    }

    private function playerTakeCards(int $count) : void
    {
        $player = $this->table->getCurrentPlayer();
        $this->log('Player ' . $player . ': Takes ' . $this->actionCount .  ' cards');
        $player->takeCards($this->table->getCardDeck(), $count);
        $this->log('Player ' . $player . ': New players cards are: ' . $player->getCards());
        $this->log('Player ' . $player . ': Finish round');
        $this->table->finishRound();
    }

    private function skipRound() : void
    {
        ++$this->actionCount;
        $player = $this->table->getCurrentPlayer();

        try {
            $card = $player->pickCardByValue(Card::VALUE_FOUR);
            $this->table->addPlayedCard($card);
            $this->log('Player ' . $player . ': Finish round');
            $this->table->finishRound();
            $this->skipRound();
        } catch (CardNotFoundException $e) {
            $player->addRoundToSkip($this->actionCount - 1);
            $this->log('Player ' . $player . ': will skip ' . ($this->actionCount - 1) . ' rounds');
            $this->table->finishRound();
            $this->log('Player ' . $player . ': Finish round');
        }
    }

    private function requestingCardValue(string $cardValue) : void
    {
        $this->log('Requested card: ' . $cardValue);
        $iteration = $this->table->countPlayers();
        for ($i = 0; $i < $iteration; $i++) {
            $player = $this->table->getCurrentPlayer();

            try {
                $cards = $player->pickCardsByValue($cardValue);
                $this->log('Player ' . $player . ': Pick cards ' . $cards);
                $this->table->addPlayedCards($cards);
            } catch (CardNotFoundException $e) {
                $this->log('Player ' . $player . ': Takes card');
                $player->takeCards($this->table->getCardDeck());
            }
            $this->table->finishRound();
        }
    }

    private function afterKing(string $color) : void
    {
        $this->actionCount += 5;

        switch ($color) {
            case Card::COLOR_HEART:
                $this->afterKingHeart();
                break;
            case Card::COLOR_SPADE:
                $this->afterKingSpade();
                break;
            default:
                break;
        }
    }

    private function afterKingHeart() : void
    {
        try {
            $card = $this->table->getCurrentPlayer()->pickCardByValueAndColor(Card::VALUE_KING, Card::COLOR_SPADE);
            $this->log('Player ' . $this->table->getCurrentPlayer() . ': Defend by king spade');
            $this->table->addPlayedCard($card);
            $this->table->finishRound();
            $this->afterKing(Card::COLOR_SPADE);
        } catch (CardNotFoundException $e) {
            $this->playerTakeCards($this->actionCount);
        }
    }

    private function afterKingSpade() : void
    {
        $this->table->backRound();
        try {
            $card = $this->table->getPreviousPlayer()->pickCardByValueAndColor(Card::VALUE_KING, Card::COLOR_HEART);
            $this->log('Player ' . $this->table->getCurrentPlayer() . ': Defend by king heart');
            $this->table->addPlayedCard($card);
            $this->afterKing(Card::COLOR_HEART);
        } catch (CardNotFoundException $e) {
            $this->table->backRound();
            $this->playerTakeCards($this->actionCount);
        }
    }

    private function changePlayedColorCardOnTable(string $color) : void
    {
        $this->table->changePlayedCardColor($color);
    }

    public function setLogger(Logger $logger) : self
    {
        $this->logger = $logger;

        return $this;
    }

    public function log($message) : void
    {
        if ($this->logger instanceof Logger) {
            $this->logger->log($message);
        }
    }
}