<?php

namespace Tests\Makao\Service;

use Makao\Card;
use Makao\Collection\CardCollection;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\GameException;
use Makao\Logger\Logger;
use Makao\Player;
use Makao\SelectedCard;
use Makao\Service\CardActionService;
use Makao\Service\CardSelector\CardSelectorInterface;
use Makao\Service\CardService;
use Makao\Service\GameService;
use Makao\Table;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{
    /** @var GameService */
    private $gameServiceUnderTest;

    /** @var MockObject | CardService $cardServiceMock */
    private $cardServiceMock;

    /** @var MockObject | CardActionService */
    private $actionServiceMock;

    /** @var MockObject | CardSelectorInterface */
    private $cardSelectorMock;

    /**
     * @throws \ReflectionException
     */
    protected function setUp() : void
    {
        $this->cardSelectorMock = $this->getMockForAbstractClass(CardSelectorInterface::class);
        $this->actionServiceMock = $this->createMock(CardActionService::class);
        $this->cardServiceMock = $this->createMock(CardService::class);
        $this->gameServiceUnderTest = new GameService(
            new Table(),
            $this->cardServiceMock,
            $this->cardSelectorMock,
            $this->actionServiceMock
        );
    }

    public function testShouldReturnFalseWhenGameIsNotStarted() {
        // When
        $actual = $this->gameServiceUnderTest->isStarted();

        // Then
        $this->assertFalse($actual);
    }

    public function testShouldReturnTrueWhenGameIsStarted() {
        // Given
        $this->gameServiceUnderTest->getTable()->addCardCollectionToDeck(new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]));

        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Max'),
        ]);

        // When
        $this->gameServiceUnderTest->startGame();

        // Then
        $this->assertTrue($this->gameServiceUnderTest->isStarted());
    }

    public function testShouldInitNewGameWithEmptyTable() {
        // When
        $table = $this->gameServiceUnderTest->getTable();

        // Then
        $this->assertSame(0, $table->countPlayers());
        $this->assertCount(0, $table->getCardDeck());
        $this->assertCount(0, $table->getPlayedCards());
    }

    public function testShouldAddPlayersToTheTable() {
        // Given
        $players = [
            new Player('Andy'),
            new Player('Tom'),
        ];

        // When
        $actual = $this->gameServiceUnderTest->addPlayers($players)->getTable();

        // Then
        $this->assertSame(2, $actual->countPlayers());
    }

    /**
     * @throws \ReflectionException
     */
    public function testShouldCreateShuffledCardDeck()
    {
        // Given
        $cardCollection = new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
        ]);

        $shuffledCardCollection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
        ]);

        $this->cardServiceMock->expects($this->once())
            ->method('createDeck')
            ->willReturn($cardCollection);

        $this->cardServiceMock->expects($this->once())
            ->method('shuffle')
            ->with($cardCollection)
            ->willReturn($shuffledCardCollection);

        // When
        /** @var Table $table */
        $table = $this->gameServiceUnderTest->prepareCardDeck();

        // Then
        $this->assertCount(2, $table->getCardDeck());
        $this->assertCount(0, $table->getPlayedCards());
        $this->assertEquals($shuffledCardCollection, $table->getCardDeck());
    }

    public function testShouldThrowGameExceptionWhenStartGameWithoutCardDeck()
    {
        // Expect
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Prepare card deck before game start');

        // When
        $this->gameServiceUnderTest->startGame();
    }

    public function testShouldThrowGameExceptionWhenStartGameWithoutMinimalPlayers()
    {
        // Given
        $this->gameServiceUnderTest->getTable()->addCardCollectionToDeck(new CardCollection([
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE)
        ]));

        // Expect
        $this->expectException(GameException::class);
        $this->expectExceptionMessage('You need minimum 2 players to start game');

        // When
        $this->gameServiceUnderTest->startGame();
    }

    public function testShouldChooseNoActionCardAsFirstPlayedCardWhenStartGame()
    {
        // Given
        $table = $this->gameServiceUnderTest->getTable();
        $noActionCard = new Card(Card::COLOR_CLUB, Card::VALUE_FIVE);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            $noActionCard,
            new Card(Card::COLOR_HEART, Card::VALUE_FIVE),
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE),
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
        ]);

        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Max'),
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($collection)
            ->willReturn($noActionCard);

        // When
        $this->gameServiceUnderTest->startGame();

        // Then
        $this->assertCount(1, $table->getPlayedCards());
        $this->assertSame($noActionCard, $table->getPlayedCards()->pickCard());
    }

    public function testShouldThrowGameExceptionWhenCardServiceThrowException()
    {
        // Expect
        $notFoundException = new CardNotFoundException('No regular cards in collection');
        $gameException = new GameException('The game needs help!', $notFoundException);

        $this->expectExceptionObject($gameException);
        $this->expectExceptionMessage('The game needs help! Issue: No regular cards in collection');

        // Given
        $table = $this->gameServiceUnderTest->getTable();
        $collection = new CardCollection([
            new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE)
        ]);
        $table->addCardCollectionToDeck($collection);

        $this->gameServiceUnderTest->addPlayers([
            new Player('Andy'),
            new Player('Max'),
        ]);

        $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($collection)
            ->willThrowException($notFoundException);

        // When
        $this->gameServiceUnderTest->startGame();
    }

    public function testShouldPlayersTakesFiveCardsFromDeckOnStartGame()
    {
        // Given
        $players = [
            new Player('Andy'),
            new Player('Tom'),
            new Player('Max'),
        ];
        $this->gameServiceUnderTest->addPlayers($players);

        $table = $this->gameServiceUnderTest->getTable();
        $noActionCard = new Card(Card::COLOR_DIAMOND, Card::VALUE_FIVE);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
            new Card(Card::COLOR_HEART, Card::VALUE_QUEEN),
            new Card(Card::COLOR_HEART, Card::VALUE_KING),
            new Card(Card::COLOR_HEART, Card::VALUE_ACE),
            new Card(Card::COLOR_SPADE, Card::VALUE_TWO),
            new Card(Card::COLOR_SPADE, Card::VALUE_THREE),
            new Card(Card::COLOR_SPADE, Card::VALUE_FOUR),
            new Card(Card::COLOR_SPADE, Card::VALUE_JACK),
            new Card(Card::COLOR_SPADE, Card::VALUE_QUEEN),
            new Card(Card::COLOR_SPADE, Card::VALUE_KING),
            new Card(Card::COLOR_SPADE, Card::VALUE_ACE),
            new Card(Card::COLOR_CLUB, Card::VALUE_TWO),
            new Card(Card::COLOR_CLUB, Card::VALUE_THREE),
            new Card(Card::COLOR_CLUB, Card::VALUE_FOUR),
            new Card(Card::COLOR_CLUB, Card::VALUE_JACK),
            new Card(Card::COLOR_CLUB, Card::VALUE_QUEEN),
            new Card(Card::COLOR_CLUB, Card::VALUE_KING),
            new Card(Card::COLOR_CLUB, Card::VALUE_ACE),
            $noActionCard,
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardServiceMock->expects($this->once())
            ->method('pickFirstNoActionCard')
            ->with($collection)
            ->willReturn($noActionCard);

        // When
        $this->gameServiceUnderTest->startGame();

        // Then
        foreach ($players as $player) {
            $this->assertCount(5, $player->getCards());
        }
    }

    public function testShouldChooseCardToPlayFromPlayerCardsAndPutItOnTheTable()
    {
        // Given
        $correctCard = new Card(Card::COLOR_HEART, Card::VALUE_FIVE);

        $player1 = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
            $correctCard,
        ]));

        $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);

        $table = $this->gameServiceUnderTest->getTable();
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_SIX);
        $table->addPlayedCard($playedCard);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardSelectorMock->expects($this->once())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor())
            ->willReturn(new SelectedCard($correctCard));

        $this->actionServiceMock->expects($this->once())
            ->method('afterCard')
            ->with($correctCard);

        // When
        $this->gameServiceUnderTest->playRound();

        // Then
        $this->assertSame($correctCard, $table->getPlayedCards()->getLastCard());
    }

    public function testShouldGivePlayerOneCardWhenHeHasNoCorrectCardToPlay()
    {
        // Given
        $player1 = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
            new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN),
        ]));

        $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);

        $table = $this->gameServiceUnderTest->getTable();
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_SIX);
        $table->addPlayedCard($playedCard);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardSelectorMock->expects($this->once())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor())
            ->willThrowException(new CardNotFoundException());

        $this->actionServiceMock->expects($this->never())
            ->method('afterCard');

        // When
        $this->gameServiceUnderTest->playRound();

        // Then
        $this->assertSame($playedCard, $table->getPlayedCards()->getLastCard());
        $this->assertCount(3, $player1->getCards());
        $this->assertCount(3, $table->getCardDeck());
        $this->assertSame($player2, $table->getCurrentPlayer());
    }

    public function testShouldSkipPlayerRoundWhenHeCanNotPlayRound()
    {
        // Given
        $player1 = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
            new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN),
        ]));

        $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);

        $table = $this->gameServiceUnderTest->getTable();
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_SIX);
        $table->addPlayedCard($playedCard);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardSelectorMock->expects($this->never())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor());

        $this->actionServiceMock->expects($this->never())
            ->method('afterCard');

        $player1->addRoundToSkip(2);

        // When
        $this->gameServiceUnderTest->playRound();

        // Then
        $this->assertSame($playedCard, $table->getPlayedCards()->getLastCard());
        $this->assertCount(2, $player1->getCards());
        $this->assertCount(4, $table->getCardDeck());
        $this->assertSame($player2, $table->getCurrentPlayer());
    }

    public function testShouldRebuildCardDeckFromPlayedCardsOnRoundBeginning()
    {
        // Given
        $player1 = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
            new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN),
        ]));

        $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);

        $table = $this->gameServiceUnderTest->getTable();
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_SIX);
        $table->addPlayedCard($playedCard);

        $collection = new CardCollection([
            new Card(Card::COLOR_HEART, Card::VALUE_TWO),
            new Card(Card::COLOR_HEART, Card::VALUE_THREE),
            new Card(Card::COLOR_HEART, Card::VALUE_FOUR),
            new Card(Card::COLOR_HEART, Card::VALUE_JACK),
        ]);

        $table->addCardCollectionToDeck($collection);

        $this->cardSelectorMock->expects($this->never())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor());

        $this->actionServiceMock->expects($this->never())
            ->method('afterCard');

        $this->cardServiceMock->expects($this->once())
            ->method('rebuildDeckFromPlayedCards')
            ->with($table->getCardDeck(), $table->getPlayedCards());

        $player1->addRoundToSkip(2);

        // When
        $this->gameServiceUnderTest->playRound();

        // Then
        $this->assertSame($playedCard, $table->getPlayedCards()->getLastCard());
        $this->assertCount(2, $player1->getCards());
        $this->assertCount(4, $table->getCardDeck());
        $this->assertSame($player2, $table->getCurrentPlayer());
    }

    public function testShouldThrowGameExceptionWhenCardServiceThrowCardNotFoundExceptionOnRebuildDeckFromPlayedCards()
    {
        // Expect
        $notFoundException = new CardNotFoundException('Played cards collection is empty. You can not rebuild deck!');
        $gameException = new GameException('The game needs help!', $notFoundException);

        $this->expectExceptionObject($gameException);
        $this->expectExceptionMessage('The game needs help! Issue: Played cards collection is empty. You can not rebuild deck!');

        // Given
        $player1 = new Player('Andy', new CardCollection([
            new Card(Card::COLOR_SPADE, Card::VALUE_EIGHT),
            new Card(Card::COLOR_SPADE, Card::VALUE_SEVEN),
        ]));

        $player2 = new Player('Max');

        $this->gameServiceUnderTest->addPlayers([$player1, $player2]);

        $table = $this->gameServiceUnderTest->getTable();
        $playedCard = new Card(Card::COLOR_HEART, Card::VALUE_SIX);
        $table->addPlayedCard($playedCard);

        $collection = new CardCollection();

        $table->addCardCollectionToDeck($collection);

        $this->cardSelectorMock->expects($this->never())
            ->method('chooseCard')
            ->with($player1, $playedCard, $table->getPlayedCardColor());

        $this->actionServiceMock->expects($this->never())
            ->method('afterCard');

        $this->cardServiceMock->expects($this->once())
            ->method('rebuildDeckFromPlayedCards')
            ->with($table->getCardDeck(), $table->getPlayedCards())
            ->willThrowException($notFoundException);

        // When
        $this->gameServiceUnderTest->playRound();
    }

    public function testShouldLogMessage()
    {
        // Given
        $message = 'MESSAGE';
        $loggerMock = $this->prophesize(Logger::class);

        $loggerMock->log($message)->shouldBeCalled();

        $this->gameServiceUnderTest->setLogger($loggerMock->reveal());

        // When
        $this->gameServiceUnderTest->log($message);

    }

    public function testShouldNotLogMessageWithoutLogger()
    {
        // Given
        $message = 'MESSAGE';
        $loggerMock = $this->prophesize(Logger::class);

        $loggerMock->log($message)->shouldNotBeCalled();

        // When
        $this->gameServiceUnderTest->log($message);
    }
}