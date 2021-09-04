<?php

namespace Tests\Makao\Collection;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\MethodNotAllowedException;
use PHPUnit\Framework\TestCase;

class CardCollectionTest extends TestCase
{
    /** @var CardCollection */
    private $cardCollection;

    protected function setUp(): void
    {
        $this->cardCollection = new CardCollection();
    }

    public function testShouldReturnZeroOnEmptyCollection()
    {
        // Then
        $this->assertCount(0, $this->cardCollection);
    }

    public function testShouldAddNewCardtoCardCollection()
    {
        // Given
        $card = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);

        // When
        $this->cardCollection->add($card);

        // Then
        $this->assertCount(1, $this->cardCollection);
    }

    public function testShouldAddNewCardsInChainToCardCollection()
    {
        // Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);

        // When
        $this->cardCollection
            ->add($firstCard)
            ->add($secondCard);

        // Then
        $this->assertCount(2, $this->cardCollection);
    }

    public function testShouldThrowCardNotFoundExceptionWhenITryPickCardFromEmptyCardCollection()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not pick card from empty CardCollection!');

        // When
        $this->cardCollection->pickCard();
    }
    
    public function testShouldIterableOnCardCollection()
    {
        // Given
        $card = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);
            
        // When & Then
        $this->cardCollection->add($card);

        $this->assertTrue($this->cardCollection->valid());
        $this->assertSame($card, $this->cardCollection->current());
        $this->assertSame(0, $this->cardCollection->key());

        $this->cardCollection->next();
        $this->assertFalse($this->cardCollection->valid());
        $this->assertSame(1, $this->cardCollection->key());

        $this->cardCollection->rewind();
        $this->assertTrue($this->cardCollection->valid());
        $this->assertSame($card, $this->cardCollection->current());
        $this->assertSame(0, $this->cardCollection->key());
    }
    
    public function testShouldGetFirstCardFromCardCollectionAndMoveThisCardFromDeck()
    {
        // Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);
        $this->cardCollection
            ->add($firstCard)
            ->add($secondCard);

        // When
        $actual = $this->cardCollection->pickCard();

        // Then
        $this->assertCount(1, $this->cardCollection);
        $this->assertSame($firstCard, $actual);
        $this->assertSame($secondCard, $this->cardCollection[0]);
    }

    public function testShouldThrowCardNotFoundExceptionWhenIPickedAllCardFromCardCollection()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not pick card from empty CardCollection!');

        //Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);
        $this->cardCollection
            ->add($firstCard)
            ->add($secondCard);

        // When
        $actual = $this->cardCollection->pickCard();
        $this->assertSame($firstCard, $actual);

        $actual = $this->cardCollection->pickCard();
        $this->assertSame($secondCard, $actual);

        $this->cardCollection->pickCard();
    }

    public function testShouldThrowMethodNotAllowedExceptionWhenYouTryAddCardToCollectionAsArray()
    {
        // Expect
        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('You can not add art to collection as array. Use addCard() method!');

        // Given
        $card = new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE);

        // When
        $this->cardCollection[] = $card;

    }

    public function testShouldReturnCollectionAsArray()
    {
        // Given
        $cards = [
             new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT),
             new Card(CARD::COLOR_DIAMOND, Card::VALUE_FIVE),
        ];
            
        // When
        $actual = new CardCollection($cards);
            
        // Then
        $this->assertEquals($cards, $actual->toArray());
    }
}