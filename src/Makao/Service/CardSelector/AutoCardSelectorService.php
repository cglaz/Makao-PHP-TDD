<?php

namespace Makao\Service\CardSelector;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Player;
use Makao\SelectedCard;
use Makao\Service\CardService;
use Makao\Validator\CardValidator;

class AutoCardSelectorService implements CardSelectorInterface
{
    /**
     * @var CardValidator
     */
    private $validator;

    /**
     * @var CardService
     */
    private $cardService;

    public function __construct(CardValidator $validator, CardService $cardService)
    {
        $this->validator = $validator;
        $this->cardService = $cardService;
    }

    /**
     * @inheritdoc
     */
    public function chooseCard(Player $player, Card $playedCard, string $acceptColor) : SelectedCard
    {
        $request = null;
        foreach ($player->getCards() as $index => $card) {
            if ($this->validator->valid($playedCard, $card, $acceptColor)) {
                if ($this->cardService->isRequestNeeded($card)) {
                    $request = $this->chooseRequestForCard($player, $card);
                }

                return new SelectedCard($player->pickCard($index), $request);
            }
        }

        throw new CardNotFoundException('Player has no cards to play');
    }

    private function chooseRequestForCard(Player $player, Card $card) : ?string
    {
        switch ($card->getValue()) {
            case Card::VALUE_JACK:
                return $this->cardService->getTheMostOccurringNoActionPlayerCardsValue($player);
                break;
            case Card::VALUE_ACE:
                return $this->cardService->getTheMostOccurringPlayerCardsColor($player);
                break;
            default:
                throw new \InvalidArgumentException('Choose request for not request needed card');
        }
    }
}