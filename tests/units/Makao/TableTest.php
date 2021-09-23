<?php

namespace Tests\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\TooManyPlayersAtTheTableException;
use Makao\Player;
use Makao\Table;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    /** @var Table */
    private $tableUnderTest;

    public function setUp() : void
    {
        $this->tableUnderTest = new Table();
    }

    public function testShouldCreateEmptyTable() {
        // Given
        $expected = 0;

        // When
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldAddOnePlayerToTable() {
        // Given
        $expected = 1;
        $player = new Player('Andy');

        // When
        $this->tableUnderTest->addPlayer($player);
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldReturnCountWhenIAddManyPlayers() {
        // Given
        $expected = 2;

        // When
        $this->tableUnderTest->addPlayer(new Player('Andy'));
        $this->tableUnderTest->addPlayer(new Player('Tom'));
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldThrowTooManyPlayersAtTheTableExceptionWhenITryAddMoreThanFourPlayers() {
        // Expect
        $this->expectException(TooManyPlayersAtTheTableException::class);
        $this->expectExceptionMessage('Max capacity is 4 players!');

        // When
        $this->tableUnderTest->addPlayer(new Player('Andy'));
        $this->tableUnderTest->addPlayer(new Player('Tom'));
        $this->tableUnderTest->addPlayer(new Player('Max'));
        $this->tableUnderTest->addPlayer(new Player('John'));
        $this->tableUnderTest->addPlayer(new Player('Michael'));
    }

    public function testShouldReturnEmptyCardCollectionForPlayedCard() {
        // When
        $actual = $this->tableUnderTest->getPlayedCards();

        // Then
        $this->assertInstanceOf(CardCollection::class, $actual);
        $this->assertCount(0, $actual);
    }

    public function testShouldPutCardDeckOnTable() {
        // Given
        $cards = new CardCollection([
            new Card(Card::COLOR_DIAMOND, Card::VALUE_KING)
        ]);

        // When
        $table = new Table($cards);
        $actual = $table->getCardDeck();

        // Then
        $this->assertSame($cards, $actual);
    }

    public function testShouldAddCardCollectionToCardDeckOnTable()
    {
        // Given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_DIAMOND, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_EIGHT),
        ]);

        // When
        $actual = $this->tableUnderTest->addCardCollectionToDeck($cardCollection);

        // Then
        $this->assertEquals($cardCollection, $actual->getCardDeck());
    }

    public function testShouldReturnCurrentPlayer()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Jack');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);

        // When
        $actual = $this->tableUnderTest->getCurrentPlayer();

        // Then
        $this->assertSame($player1, $actual);
    }

    public function testShouldReturnNextPlayer()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Jack');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);

        // When
        $actual = $this->tableUnderTest->getNextPlayer();

        // Then
        $this->assertSame($player2, $actual);
    }

    public function testShouldReturnPreviousPlayer()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Jack');
        $player4 = new Player('Bill');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);
        $this->tableUnderTest->addPlayer($player4);

        // When
        $actual = $this->tableUnderTest->getPreviousPlayer();

        // Then
        $this->assertSame($player4, $actual);
    }
    
    public function testShouldSwitchCurrentPlayerWhenRoundFinished()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Jack');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);

        // When & Then
        $this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());

        $this->tableUnderTest->finishRound();

        $this->assertSame($player2, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player1, $this->tableUnderTest->getPreviousPlayer());

        $this->tableUnderTest->finishRound();

        $this->assertSame($player3, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player1, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getPreviousPlayer());

        $this->tableUnderTest->finishRound();

        $this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());
    }

    public function testShouldAllowBackRoundOnTable()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Jack');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);

        // When & Then
        $this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());

        $this->tableUnderTest->finishRound();

        $this->assertSame($player2, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player1, $this->tableUnderTest->getPreviousPlayer());

        $this->tableUnderTest->backRound();

        $this->assertSame($player1, $this->tableUnderTest->getCurrentPlayer());
        $this->assertSame($player2, $this->tableUnderTest->getNextPlayer());
        $this->assertSame($player3, $this->tableUnderTest->getPreviousPlayer());
    }

    public function testShouldThrowCardNotFoundExceptionWhenGetPlayedCardColorOnEmptyTable()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('No played cards on the table yet!');

        // When
        $this->tableUnderTest->getPlayedCardColor();
    }

    public function testShouldReturnPlayedCardColorSetByAddPlayedCard()
    {
        // When
        $this->tableUnderTest->addPlayedCard(new Card(Card::COLOR_CLUB, Card::VALUE_FIVE));

        // Then
        $this->assertEquals(Card::COLOR_CLUB, $this->tableUnderTest->getPlayedCardColor());
    }

    public function testShouldReturnPlayedCardsColorSetByAddPlayedCard()
    {
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_FIVE),
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
        ]);

        // When
        $this->tableUnderTest->addPlayedCards($collection);

        // Then
        $this->assertEquals(Card::COLOR_DIAMOND, $this->tableUnderTest->getPlayedCardColor());
    }
}