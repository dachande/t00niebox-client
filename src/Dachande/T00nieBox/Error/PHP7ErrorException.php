<?php
namespace Dachande\T00nieBox\Error;

use Exception;

class PHP7ErrorException extends Exception
{

    /**
     * The wrapped error object
     *
     * @var \Error
     */
    protected $_error;

    /**
     * Wraps the passed Error class
     *
     * @param \Error $error the Error object
     */
    public function __construct($error)
    {
        $this->_error = $error;
        $this->message = $error->getMessage();
        $this->code = $error->getCode();
        $this->file = $error->getFile();
        $this->line = $error->getLine();
        $msg = sprintf(
            '(%s) - %s in %s on %s',
            get_class($error),
            $this->message,
            $this->file ?: 'null',
            $this->line ?: 'null'
        );
        parent::__construct($msg, $this->code);
    }

    /**
     * Returns the wrapped error object
     *
     * @return \Error
     */
    public function getError()
    {
        return $this->_error;
    }
}
