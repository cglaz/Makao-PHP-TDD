<?php

namespace Makao;

class SelectedCard
{
    /** @var Card */
    private $card;

    /** @var string */
    private $request;

    public function __construct(Card $card, string $request = null)
    {
        $this->card = $card;
        $this->request = $request;
    }

    /**
     * @return Card
     */
    public function getCard() : Card
    {
        return $this->card;
    }

    /**
     * @return string
     */
    public function getRequest() : ?string
    {
        return $this->request;
    }
}