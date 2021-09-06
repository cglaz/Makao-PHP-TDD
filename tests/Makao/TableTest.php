<?php

namespace Tests\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\TooManyPlayersAtTheTableException;
use Makao\Table;
use Makao\Player;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    /** @var Table */
    private $tableUnderTest;

    public function setUp(): void
    {
       $this->tableUnderTest = new Table();
    }

    public function testShouldCreateEmptyTable()
    {
        // Given
        $expected = 0;

        // When
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldAddOnePlayerToTable()
    {
        // Given - Zmienne wejściowe
        $expected = 1;
        $player = new Player('Andy');

        // When
        $this->tableUnderTest->addPlayer($player);
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }
    
    public function testShouldReturnCountWhenIAddManyPlayers()
    {
        // Given - Zmienne wejściowe
        $expected = 2;

        // When
        $this->tableUnderTest->addPlayer(new Player('Andy'));
        $this->tableUnderTest->addPlayer(new Player('Andys'));
        $actual = $this->tableUnderTest->countPlayers();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldThrowTooManyPlayersAtTheTableExceptionWhenITryAddMoreThanFourPlayers()
    {
        // Expect
        $this->expectException(TooManyPlayersAtTheTableException::class);
        $this->expectExceptionMessage('Max capacity is 4 players');

        // When
        $this->tableUnderTest->addPlayer(new Player('Andy'));
        $this->tableUnderTest->addPlayer(new Player('Andys'));
        $this->tableUnderTest->addPlayer(new Player('Andyss'));
        $this->tableUnderTest->addPlayer(new Player('Andysss'));
        $this->tableUnderTest->addPlayer(new Player('Andyssss'));
    }
    
    public function testShouldReturnEmptyCardCollectionForPlayedCard()
    {
        // When
        $actual = $this->tableUnderTest->getPlayedCards();

        // Then
        $this->assertInstanceOf(CardCollection::class, $actual);
        $this->assertCount(0, $actual);
    }
    
    public function testShouldPutCardDeckOnTable()
    {
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
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
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
        $player3 = new Player('Ed');

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
        $player3 = new Player('Ed');

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
        $player3 = new Player('Ed');

        $this->tableUnderTest->addPlayer($player1);
        $this->tableUnderTest->addPlayer($player2);
        $this->tableUnderTest->addPlayer($player3);

        // When
        $actual = $this->tableUnderTest->getPreviousPlayer();

        // Then
        $this->assertSame($player3, $actual);
    }

    public function testShouldSwitchCurrentPlayerWhenRoundFinished()
    {
        // Given
        $player1 = new Player('Andy');
        $player2 = new Player('Tom');
        $player3 = new Player('Ed');

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

}