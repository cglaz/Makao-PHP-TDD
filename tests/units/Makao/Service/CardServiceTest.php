<?php

namespace Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use PHPUnit\Framework\TestCase;
use Makao\Service\CardService;

class CardServiceTest extends TestCase
{
    /** @var \Makao\Service\CardService  */
    private $cardServiceUnderTest;

    /** @var ShuffleService|mixed|\PHPUnit\Framework\MockObject\MockObject | ShuffleService  */
    private $shuffleServiceMock;

    /** @throws \ReflectionException */

    protected function setUp(): void
    {
        $this->shuffleServiceMock = $this->createMock(ShuffleService::class);
        $this->cardServiceUnderTest = new CardService($this->shuffleServiceMock);
    }

    public function testShouldAllowCreateNewCardCollection()
    {
        // When
        $actual = $this->cardServiceUnderTest->createDeck();

        // Then
        $this->assertInstanceOf(CardCollection::class, $actual);
        $this->assertCount(52, $actual);

        $i = 0;
        foreach (Card::values() as $value) {
            foreach (Card::colors() as $color) {
                $this->assertEquals($value, $actual[$i]->getValue());
                $this->assertEquals($color, $actual[$i]->getColor());
                ++$i;
            }
        }

        return $actual;
    }

    /**
     * @depends testShouldAllowCreateNewCardCollection
     *
     * @param CardCollection $cardCollection
     */
    public function testShouldShuffleCardsInCardCollection(CardCollection $cardCollection)
    {
        // Given
        $this->shuffleServiceMock->expects($this->once())
            ->method('shuffle')
            ->willReturn(array_reverse($cardCollection->toArray()));

        // When
        $actual = $this->cardServiceUnderTest->shuffle($cardCollection);

        // Then
        $this->assertNotEquals($cardCollection, $actual);
        $this->assertEquals($cardCollection->pickCard(), $actual[51]);
    }

    public function testShouldPickFirstNoActionCardFromCollection()
    {
        // Given
        $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            $noActionCard
        ]);

        // When
        $actual = $this->cardServiceUnderTest->pickFirstNoActionCard($collection);

        // Then
        $this->assertCount(7, $collection);
        $this->assertSame($noActionCard, $actual);
    }

    public function testShouldThrowCardNotFoundExceptionWhenPickFirstNoActionCardFromCollectionWithOnlyActionCards()
    {
        //Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('No regular cards in colection');

        // Given
        $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

        // When
        $actual = $this->cardServiceUnderTest->pickFirstNoActionCard($collection);
    }

    public function testShouldPickFirstNoActionCardFromCollectionAndMovePreviousActionCardsOnTheEnd()
    {
        // Given
        $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);
        $collection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            $noActionCard,
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

        $expectCollection = new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
        ]);

        // When
        $actual = $this->cardServiceUnderTest->pickFirstNoActionCard($collection);

        // Then
        $this->assertCount(7, $collection);
        $this->assertSame($noActionCard, $actual);
        $this->assertEquals($expectCollection, $collection);
    }
}