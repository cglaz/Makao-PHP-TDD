<?php

namespace Tests\Makao;

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
        $player = new Player();

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
        $this->tableUnderTest->addPlayer(new Player());
        $this->tableUnderTest->addPlayer(new Player());
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
        $this->tableUnderTest->addPlayer(new Player());
        $this->tableUnderTest->addPlayer(new Player());
        $this->tableUnderTest->addPlayer(new Player());
        $this->tableUnderTest->addPlayer(new Player());
        $this->tableUnderTest->addPlayer(new Player());
    }
}