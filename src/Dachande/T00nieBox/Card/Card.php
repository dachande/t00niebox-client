<?php
namespace Dachande\T00nieBox\Card;

class Card
{
    use \Cake\Log\LogTrait;

    /**
     * Card Uuid
     *
     * @var \Dachande\T00nieBox\Uuid
     */
    protected $uuid;

    /**
     * Playlist title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Playlist share
     *
     * @var string
     */
    protected $share = '';

    /**
     * Files/Folders to download and put into the final playlist
     *
     * @var array
     */
    protected $files = [];

    /**
     * Generates card object
     *
     * @param \Dachande\T00nieBox\Uuid $uuid
     * @param string $title
     * @param string $share
     * @param array $files
     */
    public function __construct(\Dachande\T00nieBox\Uuid $uuid, string $title, string $share, array $files)
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        $this->log(sprintf('Card - Creating new card with uuid "%s" and title "%s".', $uuid->get(), $title), 'info');

        $this->uuid = $uuid;
        $this->title = $title;
        $this->share = $share;
        $this->files = $files;
    }

    /**
     * Get card uuid.
     *
     * @return \Dachande\T00nieBox\Uuid
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
     * Get playlist share
     *
     * @return string
     */
    public function getShare()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return $this->share;
    }

    /**
     * Check if card has a share set
     *
     * @return string
     */
    public function hasShare()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return (!empty($this->share)) ? true : false;
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
     * Get files/folders for playlist
     *
     * @return array
     */
    public function hasFiles()
    {
        $this->log(sprintf('%s', __METHOD__), 'debug');

        return (sizeof($this->files) > 0) ? true : false;
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
}
