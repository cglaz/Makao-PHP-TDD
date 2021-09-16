<?php

namespace Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Player;
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

    public function providerForActionCard()
    {
        return [
            'Card Value: ' . Card::VALUE_TWO => [new Card(Card::COLOR_HEART, Card::VALUE_TWO), true],
            'Card Value: ' . Card::VALUE_THREE => [new Card(Card::COLOR_HEART, Card::VALUE_THREE), true],
            'Card Value: ' . Card::VALUE_FOUR => [new Card(Card::COLOR_HEART, Card::VALUE_FOUR), true],
            'Card Value: ' . Card::VALUE_FIVE => [new Card(Card::COLOR_HEART, Card::VALUE_FIVE), false],
            'Card Value: ' . Card::VALUE_SIX  => [new Card(Card::COLOR_HEART,Card::VALUE_SIX), false],
            'Card Value: ' . Card::VALUE_SEVEN => [new Card(Card::COLOR_HEART,Card::VALUE_SEVEN), false],
            'Card Value: ' . Card::VALUE_EIGHT => [new Card(Card::COLOR_HEART,Card::VALUE_EIGHT), false],
            'Card Value: ' . Card::VALUE_NINE => [new Card(Card::COLOR_HEART,Card::VALUE_NINE), false],
            'Card Value: ' . Card::VALUE_TEN => [new Card(Card::COLOR_HEART,Card::VALUE_TEN), false],
            'Card Value: ' . Card::VALUE_JACK => [new Card(Card::COLOR_HEART,Card::VALUE_JACK), true],
            'Card Value: ' . Card::VALUE_QUEEN => [new Card(Card::COLOR_HEART,Card::VALUE_QUEEN), true],
            'Card Value: ' . Card::VALUE_KING => [new Card(Card::COLOR_HEART,Card::VALUE_KING), true],
            'Card Value: ' . Card::VALUE_ACE => [new Card(Card::COLOR_HEART,Card::VALUE_ACE), true],
        ];
    }

    /**
     * @dataProvider providerForActionCard
     *
     * @param Card $card
     * @param bool $expected
     */
    public function testShouldReturnBoolForIsActionCard(Card $card, bool $expected)
    {
        // When
        $actual = $this->cardServiceUnderTest->isAction($card);

        // Then
        $this->assertSame($expected, $actual);
    }


    public function providerForIsRequestNeeded()
    {
        return [
            'Card Value: ' . Card::VALUE_TWO => [new Card(Card::COLOR_HEART, Card::VALUE_TWO), false],
            'Card Value: ' . Card::VALUE_THREE => [new Card(Card::COLOR_HEART, Card::VALUE_THREE), false],
            'Card Value: ' . Card::VALUE_FOUR => [new Card(Card::COLOR_HEART, Card::VALUE_FOUR), false],
            'Card Value: ' . Card::VALUE_FIVE => [new Card(Card::COLOR_HEART, Card::VALUE_FIVE), false],
            'Card Value: ' . Card::VALUE_SIX   => [new Card(Card::COLOR_HEART,Card::VALUE_SIX), false],
            'Card Value: ' . Card::VALUE_SEVEN => [new Card(Card::COLOR_HEART,Card::VALUE_SEVEN), false],
            'Card Value: ' . Card::VALUE_EIGHT => [new Card(Card::COLOR_HEART,Card::VALUE_EIGHT), false],
            'Card Value: ' . Card::VALUE_NINE  => [new Card(Card::COLOR_HEART,Card::VALUE_NINE), false],
            'Card Value: ' . Card::VALUE_TEN   => [new Card(Card::COLOR_HEART,Card::VALUE_TEN), false],
            'Card Value: ' . Card::VALUE_JACK  => [new Card(Card::COLOR_HEART,Card::VALUE_JACK), true],
            'Card Value: ' . Card::VALUE_QUEEN => [new Card(Card::COLOR_HEART,Card::VALUE_QUEEN), false],
            'Card Value: ' . Card::VALUE_KING  => [new Card(Card::COLOR_HEART,Card::VALUE_KING), false],
            'Card Value: ' . Card::VALUE_ACE  => [new Card(Card::COLOR_HEART,Card::VALUE_ACE), true],
        ];
    }

    /**
     * @dataProvider providerForIsRequestNeeded
     *
     * @param Card $card
     * @param bool $expected
     */
    public function testShouldReturnBoolForIsRequestNeeded(Card $card, bool $expected)
    {
        // When
        $actual = $this->cardServiceUnderTest->isRequestNeeded($card);

        // Then
        $this->assertSame($expected, $actual);
    }
    
    public function testShouldThrowCardNotFoundExceptionWhenGetTheMostOccurringNoActionPlayerCardsValueWithoutPlayerCards()
    {
        //Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has no action cards!');

        // Given
        $player = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_HEART,Card::VALUE_KING),
            new Card(Card::COLOR_HEART,Card::VALUE_ACE),
        ]));
            
        // When
        $this->cardServiceUnderTest->getTheMostOccurringNoActionPlayerCardsValue($player);
    }
    
    
    public function testShouldReturnTheMostOccurringNoActionPlayerCardsValue()
    {
        // Given
        $expectedValue = Card::VALUE_FIVE;

        $player = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART,Card::VALUE_SIX),
            new Card(Card::COLOR_HEART,Card::VALUE_ACE),
        ]));
            
        // When
        $actual = $this->cardServiceUnderTest->getTheMostOccurringNoActionPlayerCardsValue($player);
            
        // Then
        $this->assertSame($actual, $expectedValue);
    }

    public function testShouldReturnFirstTheMostOccurringNoActionPlayerCardsValueWhenFewCardsHasTheSameCount()
    {
        // Given
        $expectedValue = Card::VALUE_SIX;

        $player = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_HEART,Card::VALUE_SIX),
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_SIX),
        ]));
            
        // When
        $actual = $this->cardServiceUnderTest->getTheMostOccurringNoActionPlayerCardsValue($player);
            
        // Then
        $this->assertSame($actual, $expectedValue);
    }

    public function testShouldThrowCardNotFoundExceptionWhenGetTheMostOccurringPlayerCardsColorWithoutPlayerCards()
    {
        //Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has no cards!');

        // Given
        $player = new Player('Andy');

        // When
        $this->cardServiceUnderTest->getTheMostOccurringPlayerCardsColor($player);
    }

    public function testShouldReturnTheMostOccurringPlayerCardsColor()
    {
        // Given
        $expectedColor = Card::COLOR_HEART;

        $player = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART,Card::VALUE_SIX),
            new Card(Card::COLOR_HEART,Card::VALUE_ACE),
        ]));

        // When
        $actual = $this->cardServiceUnderTest->getTheMostOccurringPlayerCardsColor($player);

        // Then
        $this->assertSame($actual, $expectedColor);
    }

    public function testShouldReturnFirstTheMostOccurringPlayerCardsColorWhenFewCardsHasTheSameCount()
    {
        // Given
        $expectedColor = Card::COLOR_SPADE;

        $player = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_SIX),
            new Card(Card::COLOR_HEART,Card::VALUE_ACE),
        ]));

        // When
        $actual = $this->cardServiceUnderTest->getTheMostOccurringPlayerCardsColor($player);

        // Then
        $this->assertSame($actual, $expectedColor);
    }

    public function testShouldRebuildCardDeckFromPlayedCardsCollectionWhenCardDeckIsEmpty()
    {
        // Given
        $deck = new CardCollection();
        $lastPlayedCard = new Card(Card::COLOR_HEART,Card::VALUE_ACE);
        $playedCards = new CardCollection([
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_SIX),
            $lastPlayedCard,
        ]);

        $shuffleCards = new CardCollection([
            new Card(Card::COLOR_SPADE,Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART,Card::VALUE_FIVE),
            new Card(Card::COLOR_SPADE,Card::VALUE_SIX),
        ]);

        $this->shuffleServiceMock->expects($this->once())
            ->method('shuffle')
            ->with($shuffleCards->toArray())
            ->willReturn(array_reverse($shuffleCards->toArray()));

        // When
        $this->cardServiceUnderTest->rebuildDeckFromPlayedCards($deck, $playedCards);

        // Then
        $this->assertCount(3, $deck);
        $this->assertCount(1, $playedCards);
        $this->assertSame($lastPlayedCard, $playedCards->getLastCard());
    }

    public function testShouldThrowCardNotFoundExceptionWhenRebuildCardDeckFromPlayedCardsCollectionWithEmptyPlayedCards()
    {
        //Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Played cards collection is empty. You can not rebuild deck!');

        // Given
        $deck = new CardCollection();
        $playedCards = new CardCollection();

        // When
        $this->cardServiceUnderTest->rebuildDeckFromPlayedCards($deck, $playedCards);
    }


    /**
     * @depends testShouldAllowCreateNewCardCollection
     *
     * @param CardCollection $cardCollection
     */
    public function testShouldShuffleCardsFromPlayedCardsOnRebuildDeckFromPlayedCards(CardCollection $cardCollection)
    {
        // Given
        $playedCards = clone $cardCollection;
        $lastCard = $cardCollection->pickCard(51);

        $this->shuffleServiceMock->expects($this->once())
            ->method('shuffle')
            ->with($cardCollection->toArray())
            ->willReturn(array_reverse($cardCollection->toArray()));
        $deck = new CardCollection();

        // When
        $this->cardServiceUnderTest->rebuildDeckFromPlayedCards($deck, $playedCards);

        // Then
        $this->assertCount(51, $deck);
        $this->assertCount(1, $playedCards);
        $this->assertSame($lastCard, $playedCards->getLastCard());
    }

}