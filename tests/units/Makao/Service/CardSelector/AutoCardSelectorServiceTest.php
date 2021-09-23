<?php

namespace Tests\Makao\Service\CardSelector;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Player;
use Makao\SelectedCard;
use Makao\Service\CardSelector\AutoCardSelectorService;
use Makao\Service\CardService;
use Makao\Validator\CardValidator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class AutoCardSelectorServiceTest extends TestCase
{
    /** @var ObjectProphecy | CardValidator */
    private $validatorMock;

    /** @var ObjectProphecy | CardService */
    private $cardServiceMock;

    public function setUp() : void
    {
        $this->validatorMock = $this->prophesize(CardValidator::class);
        $this->cardServiceMock = $this->prophesize(CardService::class);
    }

    public function testShouldThrowCardNotFoundExceptionWhenPlayersHasNoCards()
    {
        // Expect
        $this->expectException(CardNotFoundException::class);
        $this->expectExceptionMessage('Player has no cards to play!');

        // Given
        $player = new Player('Andy');
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->cardServiceMock->isRequestNeeded(Argument::any())->shouldNotBeCalled();

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());
    }

    public function testShouldReturnSelectedCardWhenPlayerHasRegularCardInHand()
    {
        // Given
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_FIVE);
        $player = new Player('Andy', new CardCollection([$newCard]));

        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid($playedCard, $newCard, $playedCard->getColor())->willReturn(true);
        $this->cardServiceMock->isRequestNeeded($newCard)->willReturn(false);

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $actual = $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());

        // Then
        $this->assertEquals(new SelectedCard($newCard), $actual);
        $this->assertCount(0, $player->getCards());

        $this->cardServiceMock->getTheMostOccurringNoActionPlayerCardsValue(Argument::any())->shouldNotHaveBeenCalled();
        $this->cardServiceMock->getTheMostOccurringPlayerCardsColor(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testShouldReturnCorrectSelectedCardWhenPlayerHasMoreRegularCardInHand()
    {
        // Given
        $wrongCard = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_FIVE);
        $player = new Player('Andy', new CardCollection([$wrongCard, $newCard]));

        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid($playedCard, $wrongCard, $playedCard->getColor())->willReturn(false);
        $this->validatorMock->valid($playedCard, $newCard, $playedCard->getColor())->willReturn(true);
        $this->cardServiceMock->isRequestNeeded($newCard)->willReturn(false);

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $actual = $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());

        // Then
        $this->assertEquals(new SelectedCard($newCard), $actual);
        $this->assertCount(1, $player->getCards());
        $this->assertSame($wrongCard, $player->getCards()->getLastCard());

        $this->cardServiceMock->getTheMostOccurringNoActionPlayerCardsValue(Argument::any())->shouldNotHaveBeenCalled();
        $this->cardServiceMock->getTheMostOccurringPlayerCardsColor(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testShouldReturnSelectedCardWithRequestWhenPlayerUseJackCard()
    {
        // Given
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_JACK);
        $requestedCard = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);
        $player = new Player('Andy', new CardCollection([$newCard, $requestedCard]));

        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid($playedCard, $newCard, $playedCard->getColor())->willReturn(true);
        $this->cardServiceMock->isRequestNeeded($newCard)->willReturn(true);
        $this->cardServiceMock->getTheMostOccurringNoActionPlayerCardsValue($player)->willReturn($requestedCard->getValue());

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $actual = $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());

        // Then
        $this->assertEquals(new SelectedCard($newCard, $requestedCard->getValue()), $actual);
        $this->assertCount(1, $player->getCards());

        $this->cardServiceMock->getTheMostOccurringPlayerCardsColor(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testShouldReturnSelectedCardWithRequestWhenPlayerUseAceCard()
    {
        // Given
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_ACE);
        $requestedCard = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);
        $player = new Player('Andy', new CardCollection([$newCard, $requestedCard]));

        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid($playedCard, $newCard, $playedCard->getColor())->willReturn(true);
        $this->cardServiceMock->isRequestNeeded($newCard)->willReturn(true);
        $this->cardServiceMock->getTheMostOccurringPlayerCardsColor($player)->willReturn($requestedCard->getColor());

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $actual = $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());

        // Then
        $this->assertEquals(new SelectedCard($newCard, $requestedCard->getColor()), $actual);
        $this->assertCount(1, $player->getCards());

        $this->cardServiceMock->getTheMostOccurringNoActionPlayerCardsValue(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function testShouldThrowInvalidArgumentExceptionWhenTryGetRequestForDifferentCardThanJackOrAce()
    {
        // Expect
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Choose request for not request needed card');

        // Given
        $newCard = new Card(Card::COLOR_HEART, Card::VALUE_SEVEN);
        $requestedCard = new Card(Card::COLOR_SPADE, Card::VALUE_FIVE);
        $player = new Player('Andy', new CardCollection([$newCard, $requestedCard]));

        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_TEN);

        $this->validatorMock->valid($playedCard, $newCard, $playedCard->getColor())->willReturn(true);
        $this->cardServiceMock->isRequestNeeded($newCard)->willReturn(true);
        $this->cardServiceMock->getTheMostOccurringNoActionPlayerCardsValue(Argument::any())->shouldNotBeCalled();
        $this->cardServiceMock->getTheMostOccurringPlayerCardsColor(Argument::any())->shouldNotBeCalled();

        $selectorServiceUnderTest = new AutoCardSelectorService(
            $this->validatorMock->reveal(),
            $this->cardServiceMock->reveal()
        );

        // When
        $selectorServiceUnderTest->chooseCard($player, $playedCard, $playedCard->getColor());
    }
}
