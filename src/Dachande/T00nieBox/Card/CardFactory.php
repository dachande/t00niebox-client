<?php

namespace Dachande\T00nieBox\Card;

use Dachande\T00nieBox\Uuid;
use Dachande\T00nieBox\Exception\MalformedJsonException;

class CardFactory
{
    /**
     * @var string
     */
    protected static $defaultCardTitle = 'Unknown title';

    /**
     * @var string
     */
    protected static $emptyCardTitle = 'Empty card';

    /**
     * @var string
     */
    protected static $defaultShare = '/';

    /**
     * Create new card using JSON encoded data
     *
     * @param string $cardData
     * @return \Dachande\T00nieBox\Card\Card
     */
    public static function createFromJson(string $cardData): \Dachande\T00nieBox\Card\Card
    {
        $cardDataArray = json_decode($cardData, true);

        if ($cardDataArray === null) {
            throw new MalformedJsonException('Decoding json to array failed.');
        }

        return static::createFromArray(json_decode($cardData, true));
    }

    /**
     * Create new card using array of data
     *
     * @param array $cardData
     * @return \Dachande\T00nieBox\Card\Card
     */
    public static function createFromArray(array $cardData): \Dachande\T00nieBox\Card\Card
    {
        if (!array_key_exists('card', $cardData)) {
            throw new \InvalidArgumentException('Array key "card" is missing.');
        }

        if (!is_array($cardData['card'])) {
            throw new \InvalidArgumentException('Card data empty or invalid.');
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
        $share = (!empty($cardData['card']['share'])) ? $cardData['card']['share'] : static::$defaultShare;

        return new Card(new Uuid($cardData['card']['uuid']), $title, $share, $files);
    }

    /**
     * Create new empty card with the specified uuid
     *
     * @param \Dachande\T00nieBox\Uuid $uuid
     * @return \Dachande\T00nieBox\Card\Card
     */
    public static function createEmpty(Uuid $uuid): \Dachande\T00nieBox\Card\Card
    {
        return new Card($uuid, static::$emptyCardTitle, static::$defaultShare, []);
    }
}
