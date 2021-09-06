<?php

namespace Makao\Collection;

use Makao\Card;
use Makao\Exception\CardNotFoundException;
use Makao\Exception\MethodNotAllowedException;

class CardCollection implements \Countable, \Iterator, \ArrayAccess
{
    const FIRST_CARD_INDEX = 0;

    private $cards = [];
    private $position = 0;

    public function __construct(array $cards = [])
    {
        $this->cards = $cards;
    }


    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return count($this->cards);
    }

    public function add(Card $card) : self
    {
        $this->cards[] = $card;

        return $this;
    }

    public function addCollection(CardCollection $cardCollection) : self
    {
        foreach ($cardCollection->toArray() as $card) {
            $this->add($card);
        }

        return $this;
    }

    public function pickCard(int $index = self::FIRST_CARD_INDEX) : Card
    {
        if (empty($this->cards)) {
            throw new CardNotFoundException('You can not pick card from empty CardCollection!');
        }

        $pickedCard = $this->offsetGet($index);
        $this->offsetUnset($index);
        $this->cards = array_values($this->cards);

        return $pickedCard;
    }

    /**
     * @inheritdoc
     */
    public function valid() : bool
    {
        return $this->offsetExists($this->position);
    }

    /**
     * @inheritdoc
     */
    public function current() : ?Card
    {
        return $this->cards[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function next() : void
    {
        ++$this->position;
    }

    /**
     * @inheritdoc
     */
    public function key() : int
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function rewind() : void
    {
        $this->position = self::FIRST_CARD_INDEX;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->cards[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) : Card
    {
        return $this->cards[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) : void
    {
        throw new MethodNotAllowedException('You can not add art to collection as array. Use addCard() method!');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) : void
    {
       unset($this->cards[$offset]);
    }

    public function toArray() : array
    {
        return $this->cards;
    }

}