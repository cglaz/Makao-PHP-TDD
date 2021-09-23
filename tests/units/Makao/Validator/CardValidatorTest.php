<?php

namespace Tests\Makao\Validator;

use Makao\Card;
use Makao\Exception\CardDuplicationException;
use Makao\Validator\CardValidator;
use PHPUnit\Framework\TestCase;

class CardValidatorTest extends TestCase
{
    /** @var CardValidator */
    private $cardValidatorUnderTest;

    protected function setUp() : void
    {
        $this->cardValidatorUnderTest = new CardValidator();
    }

    public function cardsProvider()
    {
        return [
            'Return True When Valid Cards With The Same Colors' => [
                new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
                true
            ],
            'Return False When Valid Cards With Different Colors and Values' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
                false
            ],
            'Return True When Valid Cards With The Same Values' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
                new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
                true
            ],
            'Queens for all' => [
                new Card(Card::COLOR_SPADE, Card::VALUE_TEN),
                new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
                true
            ],
            'All for Queens' => [
                new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
                new Card(Card::COLOR_SPADE, Card::VALUE_TEN),
                true
            ],
        ];
    }

    /**
     * @dataProvider cardsProvider
     *
     * @param Card $activeCard
     * @param Card $newCard
     * @param bool $expected
     *
     * @throws CardDuplicationException
     */
    public function testShouldValidCards(Card $activeCard, Card $newCard, bool $expected)
    {
        // When
        $actual = $this->cardValidatorUnderTest->valid($activeCard, $newCard, $activeCard->getColor());

        // Then
        $this->assertSame($expected, $actual);
    }

    public function testShouldThrowCardDuplicationExceptionWhenValidCardsAreTheSame()
    {
        // Expect
        $this->expectException(CardDuplicationException::class);
        $this->expectExceptionMessage('Valid card get the same cards: 5 spade');

        // Given
        $card = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);

        // When
        $this->cardValidatorUnderTest->valid($card, $card, $card->getColor());
    }

    public function testShouldReturnTrueWhenAceChangeAcceptColorToDifferent()
    {
        // Given
        $requestedColor = Card::COLOR_HEART;
        $playedCard = new Card(Card::COLOR_SPADE, Card::VALUE_ACE);
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        // When
        $actual = $this->cardValidatorUnderTest->valid($playedCard, $newCard, $requestedColor);

        // Then
        $this->assertTrue($actual);
    }

    public function testShouldReturnFalseWhenAceChangeAcceptColorToDifferentAndPlayerTryPutTheSameColorCard()
    {
        // Given
        $requestedColor = Card::COLOR_HEART;
        $playedCard = new Card(Card::COLOR_SPADE, Card::VALUE_ACE);
        $newCard = new Card(Card::COLOR_SPADE, Card::VALUE_TEN);

        // When
        $actual = $this->cardValidatorUnderTest->valid($playedCard, $newCard, $requestedColor);

        // Then
        $this->assertFalse($actual);
    }
}