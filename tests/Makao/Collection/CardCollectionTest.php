<?php

namespace Tests\Makao\Collection;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
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
        $card = new Card();

        // When
        $this->cardCollection->add($card);

        // Then
        $this->assertCount(1, $this->cardCollection);
    }

    public function testShouldAddNewCardsInChainToCardCollection()
    {
        // Given
        $firstCard = new Card();
        $secondCard = new Card();

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
        $card = new Card();
            
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
}