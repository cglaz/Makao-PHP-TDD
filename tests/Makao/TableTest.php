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
}