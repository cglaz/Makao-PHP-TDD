<?php

namespace Makao\Validator;

use Makao\Exception\CardDuplicationException;
use Makao\Validator\CardValidator;
use Makao\Card;
use PHPUnit\Framework\TestCase;

class CardValidatorTest extends TestCase
{
    /** @var CardValidator */
    private $cardValidator;

    protected function setUp(): void
    {
        $this->cardValidator = new CardValidator();
    }

    public function cardsProvider()
    {
        return [
            'Return True When Valid Cards With The Same Colors' => [
                new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
                new Card(Card::COLOR_DIAMOND, Card::VALUE_FOUR),
                true
            ],
            'Return False When Valid Cards With The Different Colors And Values' => [
                new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
                new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
                false
            ],
            'Return True When Valid Cards With The Same Values' => [
                new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
                new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
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
     */
    //public function (Card $activeCard, Card $newCard, bool $expected)
    public function testShouldValidCards(Card $activeCard, Card $newCard, bool $expected)
    {
        // When
        $actual = $this->cardValidator->valid($activeCard, $newCard);

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
        $this->cardValidator->valid($card, $card);
    }
}