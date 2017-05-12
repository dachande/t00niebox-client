<?php
namespace Dachande\T00nieBox;

use Cake\Log\Log;
use Cake\Core\Configure;
use Exception;
use Streamer\Stream;

class ErrorHandler
{
    public function register()
    {
        set_exception_handler([$this, 'handleException']);
    }

    public function handleException(Exception $exception)
    {
        $this->displayException($exception);
        $this->logException($exception);
    }

    protected function displayException(Exception $exception)
    {
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

    protected function logException(Exception $exception)
    {
        return Log::error($this->getMessage($exception));
    }

    protected function getMessage(Exception $exception)
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
