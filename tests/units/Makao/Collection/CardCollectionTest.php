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
    private $cardCollectionUnderTest;

    protected function setUp() : void
    {
        $this->cardCollectionUnderTest = new CardCollection();
    }

    public function testShouldReturnZeroOnEmptyCollection() {
        // Then
        $this->assertCount(0, $this->cardCollectionUnderTest);
    }

    public function testShouldAddNewCardToCardCollection() {
        // Given
        $card = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);

        // When
        $this->cardCollectionUnderTest->add($card);

        // Then
        $this->assertCount(1, $this->cardCollectionUnderTest);
    }

    public function testShouldAddNewCardsInChainToCardCollection() {
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(Card::COLOR_HEART, Card::VALUE_ACE);

        // When
        $this->cardCollectionUnderTest
            ->add($firstCard)
            ->add($secondCard);

        // Then
        $this->assertCount(2, $this->cardCollectionUnderTest);
    }

    public function testShouldThrowCardNotFoundExceptionWhenITryPickCardFromEmptyCardCollection() {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not pick card form empty CardCollection!');

        // When
        $this->cardCollectionUnderTest->pickCard();
    }

    public function testShouldIterableOnCardCollection() {
        // Given
        $card = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);

        // When & Then
        $this->cardCollectionUnderTest->add($card);

        $this->assertTrue($this->cardCollectionUnderTest->valid());
        $this->assertSame($card, $this->cardCollectionUnderTest->current());
        $this->assertSame(0, $this->cardCollectionUnderTest->key());

        $this->cardCollectionUnderTest->next();
        $this->assertFalse($this->cardCollectionUnderTest->valid());
        $this->assertSame(1, $this->cardCollectionUnderTest->key());

        $this->cardCollectionUnderTest->rewind();
        $this->assertTrue($this->cardCollectionUnderTest->valid());
        $this->assertSame($card, $this->cardCollectionUnderTest->current());
        $this->assertSame(0, $this->cardCollectionUnderTest->key());
    }

    public function testShouldGetFirstCardFromCardCollectionAndRemoveThisCardFromDeck() {
        // Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(Card::COLOR_SPADE, Card::VALUE_ACE);
        $this->cardCollectionUnderTest
            ->add($firstCard)
            ->add($secondCard);

        // When
        $actual = $this->cardCollectionUnderTest->pickCard();

        // Then
        $this->assertCount(1, $this->cardCollectionUnderTest);
        $this->assertSame($firstCard, $actual);
        $this->assertSame($secondCard, $this->cardCollectionUnderTest[0]);
    }

    public function testShouldThrowCardNotFoundExceptionWhenIPickedAllCardFromCardCollection() {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not pick card form empty CardCollection!');

        // Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);

        $this->cardCollectionUnderTest
            ->add($firstCard)
            ->add($secondCard);

        // When
        $actual = $this->cardCollectionUnderTest->pickCard();
        $this->assertSame($firstCard, $actual);

        $actual = $this->cardCollectionUnderTest->pickCard();
        $this->assertSame($secondCard, $actual);

        $this->cardCollectionUnderTest->pickCard();
    }

    public function testShouldReturnChosenCardPickedFromCollection()
    {
        // Given
        $firstCard = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);
        $secondCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);

        $this->cardCollectionUnderTest
            ->add($firstCard)
            ->add($secondCard);

        // When
        $actual = $this->cardCollectionUnderTest->pickCard(1);

        // Then
        $this->assertSame($secondCard, $actual);
    }

    public function testShouldThrowMethodNotAllowedExceptionWhenYouTryAddCardToCollectionAsArray() {
        // Expect
        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('You can not add card to collection as array. Use addCard() method!');

        // Given
        $card = new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT);

        // When
        $this->cardCollectionUnderTest[] = $card;
    }

    public function testShouldReturnCollectionAsArray() {
        // Given
        $cards = [
            new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT),
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
        ];

        // When
        $actual = new CardCollection($cards);

        // Then
        $this->assertEquals($cards, $actual->toArray());
    }

    public function testShouldAddCardCollectionToCardCollection()
    {
        // Given
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT),
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
        ]);

        // When
        $actual = $this->cardCollectionUnderTest->addCollection($collection);

        // Then
        $this->assertEquals($collection, $actual);
    }

    public function testShouldReturnLastCardFromCollectionWithoutPicking()
    {
        // Given
        $lastCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_EIGHT),
            $lastCard,
        ]);

        // When
        $actual = $collection->getLastCard();

        // Then
        $this->assertSame($lastCard, $actual);
        $this->assertCount(2, $collection);
    }

    public function testShouldThrowCardNotFoundExceptionWhenTryGetLastCardFromEmptyCollection() {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('You can not get last card form empty CardCollection!');

        // When
        $this->cardCollectionUnderTest->getLastCard();
    }
}