<?php
namespace Dachande\T00nieBox\Error;

use Cake\Log\Log;
use Cake\Core\Configure;
use Exception;
use Streamer\Stream;
use Dachande\T00nieBox\Error\PHP7ErrorException;

class ErrorHandler
{
    public function register()
    {
        set_exception_handler([$this, 'handleException']);
    }

    public function handleException($exception)
    {
        if ($exception instanceof Error) {
            $exception = new PHP7ErrorException($exception);
        }

        $this->displayException($exception);
        $this->logException($exception);
    }

    protected function displayException($exception)
    {
        if ($exception instanceof PHP7ErrorException) {
            $exception = $exception->getError();
        }
        $message = sprintf(
            "[%s] %s in [%s, line %s]",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $stream = new Stream(fopen('php://stderr', 'w'));
        $stream->write($message . "\n");
    }

    protected function logException($exception)
    {
        return Log::error($this->getMessage($exception));
    }

    protected function getMessage($exception)
    {
        $message = sprintf(
            '[%s] %s in %s on line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $debug = Configure::read('debug');

        if ($debug && method_exists($exception, 'getAttributes')) {
            $attributes = $exception->getAttributes();
            if ($attributes) {
                $message .= "\nException Attributes: " . var_export($exception->getAttributes(), true);
            }
        }

        return $message;
    }
}
