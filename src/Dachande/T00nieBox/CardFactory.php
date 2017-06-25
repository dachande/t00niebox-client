<?php

namespace Dachande\T00nieBox;

use Dachande\T00nieBox\Card;
use Dachande\T00nieBox\Exception\MalformedJsonException;

class CardFactory
{
    protected static $defaultCardTitle = 'Unknown Title';

    public static function createFromJson($cardData)
    {
        if (!is_string($cardData)) {
            throw new \InvalidArgumentException('Argument needs to be of type string.');
        }
        $cardDataArray = json_decode($cardData, true);

        if ($cardDataArray === null) {
            throw new MalformedJsonException('Decoding json to array failed.');
        }

        return static::createFromArray(json_decode($cardData, true));
    }

    public static function createFromArray(array $cardData)
    {
        if (!array_key_exists('card', $cardData)) {
            throw new \InvalidArgumentException('Array key "card" is missing.');
        }

        if (!array_key_exists('uuid', $cardData['card'])) {
            throw new \InvalidArgumentException('Card data does not contain a uuid.');
        }

        if (!array_key_exists('title', $cardData['card'])) {
            throw new \InvalidArgumentException('Card data does not contain a title.');
        }

        if (array_key_exists('files_array', $cardData['card'])) {
            $files = $cardData['card']['files_array'];
        } elseif (array_key_exists('files', $cardData['card'])) {
            $files = unserialize($cardData['card']['files']);
        } else {
            throw new \InvalidArgumentException('Card data does not contain any files.');
        }

        $title = (!empty($cardData['card']['title'])) ? $cardData['card']['title'] : static::$defaultCardTitle;

        return new Card($cardData['card']['uuid'], $title, $files);
    }
}
