<?php

namespace Tests\Makao;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
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

    public function testShouldAllowPickChosenCardFromPlayerCardCollection()
    {
        // Given
        $firstCard = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
        $secondCard = new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT);
        $thirdCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_KING);
        $cardCollection = new CardCollection([$firstCard, $secondCard, $thirdCard]);

        $player = new Player('Andy',$cardCollection);

        // When
        $actual = $player->pickCard(2);

        // Then
        $this->assertSame($thirdCard, $actual);
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

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardByValueAndHasNotCorrectCardInHand()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not card with value 2');

        // Given
        $player = new Player('Andyy');

        // When
        $player->pickCardByValue(Card::VALUE_TWO);
    }
    
    public function testShouldReturnPickCardByValueWhenPlayerHasCorrectCard()
    {
        // Given
        $card =  new Card(Card::COLOR_HEART, Card::VALUE_TWO);
        $player = new Player('Andyy', new CardCollection([
            $card
        ]));

        // When
        $actual = $player->pickCardByValue(Card::VALUE_TWO);
            
        // Then
        $this->assertSame($card, $actual);
    }

    public function testShouldReturnFirstCardByPickCardByValueWhenPlayerHasMoreCorrectCard()
    {
        // Given
        $card =  new Card(Card::COLOR_HEART, Card::VALUE_TWO);
        $player = new Player('Andyy', new CardCollection([
            $card,
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO)
        ]));

        // When
        $actual = $player->pickCardByValue(Card::VALUE_TWO);

        // Then
        $this->assertSame($card, $actual);
    }
    
    public function testShouldReturnTrueWhenPlayerCanPlayRound()
    {
        // Given
        $player = new Player('Andyy');
            
        // When
        $actual = $player->canPlayRound();
            
        // Then
        $this->assertTrue($actual);
    }
    
    public function testShouldReturnFalseWhenPlayerCanNotPlayRound()
    {
        // Given
        $player = new Player('Andyy');

        // When
        $player->addRoundToSkip();

        // Then
        $this->assertFalse($player->canPlayRound());
    }

    public function testShouldSkipManyRoundsAndBackToPlayAfter()
    {
        // Given
        $player = new Player('Andyy');

        // When & Then
        $this->assertTrue($player->canPlayRound());

        $player->addRoundToSkip(2);
        $this->assertFalse($player->canPlayRound());
        $this->assertSame(2, $player->getRoundToSkip());

        $player->skipRound();
        $this->assertFalse($player->canPlayRound());
        $this->assertSame(1, $player->getRoundToSkip());

        $player->skipRound();
        $this->assertTrue($player->canPlayRound());
        $this->assertSame(0, $player->getRoundToSkip());
    }

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardsByValueAndHasNotCorrectCardInHand()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not card with value 2');

        // Given
        $player = new Player('Andyy');

        // When
        $player->pickCardsByValue(Card::VALUE_TWO);
    }

    public function testShouldReturnPickCardsByValueWhenPlayerHasCorrectCard()
    {
        // Given
        $cardCollection =  new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO)
        ]);

        $player = new Player('Andyy', clone $cardCollection);

        // When
        $actual = $player->pickCardsByValue(Card::VALUE_TWO);

        // Then
        $this->assertEquals($cardCollection, $actual);
    }
    public function testShouldReturnFirstCardByPickCardsByValueWhenPlayerHasMoreCorrectCard()
    {
        // Given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
        ]);

        $player = new Player('Andyy', clone $cardCollection);

        // When
        $actual = $player->pickCardsByValue(Card::VALUE_TWO);

        // Then
        $this->assertEquals($cardCollection, $actual);
    }

    public function testShouldThrowCardNotFoundExceptionWhenPlayerTryPickCardByValueAndColorAndHasNotCorrectCardInHand()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has not card with value 2 and color heart');

        // Given
        $player = new Player('Andyy');

        // When
        $player->pickCardByValueAndColor(Card::VALUE_TWO, Card::COLOR_HEART);
    }

    public function testShouldReturnPickCardByValueAndColorWhenPlayerHasCorrectCard()
    {
        // Given
        $card =  new Card(Card::COLOR_HEART, Card::VALUE_TWO);
        $player = new Player('Andyy', new CardCollection([
            $card
        ]));

        // When
        $actual = $player->pickCardByValueAndColor(Card::VALUE_TWO, Card::COLOR_HEART);

        // Then
        $this->assertSame($card, $actual);
    }

}