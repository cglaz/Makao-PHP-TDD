<?php

namespace Tests\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    public function testShouldWritePlayerName()
    {
        // Given
        $player = new Player('Andy');

        // When
        ob_start();
        echo $player;
        $actual = ob_get_clean();

        // Then
        $this->assertEquals('Andy', $actual);
    }

    public function testShouldReturnPlayerCardCollcetion()
    {
        // Given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
        ]);
        $player = new Player('Andy', $cardCollection);

        // When
        $actual = $player->getCards();

        // Then
        $this->assertSame($cardCollection, $actual);
    }

    public function testShouldAllowPlayerTakeCardFromDeck()
    {
        // Given
        $card = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
        $cardCollection = new CardCollection([$card]);
        $player = new Player('Andy');

        // When
        $actual = $player->takeCards($cardCollection)->getCards();

        // Then
        $this->assertCount(0, $cardCollection);
        $this->assertCount(1, $actual);
        $this->assertSame($card, $actual[0]);
    }
    
    public function testShouldAllowPlayerTakeManyCardsFromCardCollection()
    {
        // Given
        $firstCard = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
        $secondCard = new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT);
        $thirdCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_KING);
        $cardCollection = new CardCollection([$firstCard, $secondCard, $thirdCard]);

        $player = new Player('Andy');

        // When
        $actual = $player->takeCards($cardCollection, 2)->getCards();

        // Then
        $this->assertCount(1, $cardCollection);
        $this->assertCount(2, $actual);

        $this->assertSame($firstCard, $actual->pickCard());
        $this->assertSame($secondCard, $actual->pickCard());
        $this->assertSame($thirdCard, $cardCollection->pickCard());
    }

    public function testShouldAllowPlayerSaysMakao()
    {
        // Given
        $player = new Player('Andy');

        // When
        $actual = $player->sayMakao();

        // Then
        $this->assertEquals('Makao', $actual);
    }
}