<?php

require_once __DIR__ . '/../vendor/autoload.php';

// dependencies
$table = new \Makao\Table();

$cardActionService = new \Makao\Service\CardActionService($table);

$cardService = new \Makao\Service\CardService(
    new \Makao\Service\ShuffleService()
);

$cardSelectorService = new \Makao\Service\CardSelector\AutoCardSelectorService(
    new \Makao\Validator\CardValidator(),
    $cardService
);

$gameService = new \Makao\Service\GameService(
    $table,
    $cardService,
    $cardSelectorService,
    $cardActionService
);

// setup

$gameService->prepareCardDeck();

$gameService->addPlayers([
    new \Makao\Player('Andy'),
    new \Makao\Player('Tom'),
    new \Makao\Player('Max'),
]);

try {
    $gameService->startGame();

    while (0 !== $gameService->getTable()->getCurrentPlayer()->getCards()->count()) {
        $gameService->playRound();
    }

    echo 'Winner is ' . $gameService->getTable()->getCurrentPlayer();

} catch (\Makao\Exception\GameException $e) {
    echo PHP_EOL . $e->getMessage() . PHP_EOL;
}