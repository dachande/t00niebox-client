<?php
namespace Dachande\T00nieBox;

class Card
{
    use \Cake\Log\LogTrait;

    /**
     * Card Uuid
     *
     * @var string
     */
    protected $uuid = '';

    /**
     * Playlist title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Files/Folders to download and put into the final playlist
     *
     * @var array
     */
    protected $files = [];

    /**
     * Generates card object
     *
     * @param string $uuid
     * @param string $title
     * @param array $files
     */
    public function __construct($uuid, $title, $files = [])
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log(sprintf('Creating new card with uuid "%s" and title "%s".', $uuid, $title), 'info');

        $this->uuid = $uuid;
        $this->title = $title;
        $this->files = $files;
    }

    /**
     * Generate card object from card array that has been retrieved from a t00niebox server.
     *
     * @param  array $card
     * @return \Dachande\T00nieBox\Card
     * @throws \InvalidArgumentException
     */
    public static function create($card)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (!is_array($card) ||
            !array_key_exists('card', $card)
        ) {
            throw static::generateInvalidArgumentException();
        }

        if ($card['card'] === null ||
            !array_key_exists('uuid', $card['card']) ||
            !array_key_exists('title', $card['card'])
        ) {
            throw static::generateInvalidArgumentException();
        }

        if (array_key_exists('files_array', $card['card'])) {
            $files = $card['card']['files_array'];
        } elseif (array_key_exists('files', $card['card'])) {
            $files = unserialize($card['card']['files']);
        } else {
            throw static::generateInvalidArgumentException();
        }

        return new static($card['card']['uuid'], $card['card']['title'], $files);
    }

    /**
     * Get card uuid.
     * @return string
     */
    public function getUuid()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return $this->uuid;
    }

    /**
     * Get playlist title
     *
     * @return string
     */
    public function getTitle()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return $this->title;
    }

    /**
     * Get files/folders for playlist
     *
     * @return array
     */
    public function getFiles()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return $this->files;
    }

    /**
     * Set files/folders for playlist
     *
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->files = $files;
    }

    /**
     * Returns an InvalidArgumentException
     *
     * @return \InvalidArgumentException
     */
    protected static function generateInvalidArgumentException()
    {
        return new \InvalidArgumentException('Card array structure invalid.');
    }
}
