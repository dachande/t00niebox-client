<?php
namespace Dachande\T00nieBox;

use Cake\Core\Configure;
use AFM\Rsync\Rsync as BaseRsync;
use AFM\Rsync\Command as RsyncCommand;

class Rsync
{
    use \Cake\Log\LogTrait;

    /**
     * @var \AFM\Rsync\Command
     */
    protected static $rsyncCommand = null;

    /**
     * Initialize Rsync
     *
     * This method does a full rsync command initialization. If you set $sync to false
     * the rsync command is used for listing the source files instead of synchronizing them
     * with the target.
     *
     * This will not execute the rsync command. To execute the command use the execute() method on
     * the returned object.
     *
     * @param bool $sync
     * @param string $sourceBasePath
     * @param string $filesFrom
     */
    public static function initialize($sync = true, $sourceBasePath = '/', $filesFrom = null)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        // Set Rsync source
        $source = Configure::read('Rsync.source') . ':' . escapeshellarg($sourceBasePath);
        $sourceUsername = Configure::read('Rsync.sourceUsername');
        if ($sourceUsername !== null) {
            $source = $sourceUsername . '@' . $source;
        }
        $source = '"' . $source . '"';

        // Set Rsync target
        $target = Configure::read('Rsync.target');
        $targetUsername = Configure::read('Rsync.targetUsername');
        if ($targetUsername !== null) {
            $target = $targetUsername . '@' . $target;
        }

        // Initialize rsync
        $rsync = new BaseRsync();
        static::$rsyncCommand = $rsync->getCommand($source, $target);

        // Add options
        $options = Configure::read('Rsync.config.options');
        if ($options !== null && is_array($options)) {
            foreach ($options as $option) {
                static::$rsyncCommand->addOption($option);
            }
        }

        // Add arguments
        $arguments = Configure::read('Rsync.config.arguments');
        if ($arguments !== null && is_array($arguments)) {
            foreach ($arguments as $argument => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        static::$rsyncCommand->addArgument($argument, $subValue);
                    }
                } else {
                    static::$rsyncCommand->addArgument($argument, $value);
                }
            }
        }

        if ($sync === false) {
            static::$rsyncCommand->addArgument('list-only');
        }

        if ($filesFrom !== null) {
            static::$rsyncCommand->addArgument('files-from', $filesFrom);
        }
    }

    /**
     * Execute the rsync command
     *
     * If $returnOutput is set to false the results of the rsync command will be
     * piped to stdout using a simple shell_exec() call. Otherwise the output
     * will be returned as a string.
     *
     * @param bool $returnOutput
     * @return string|void
     */
    public static function execute($returnOutput = true)
    {
        static::log(sprintf('%s', __METHOD__), 'debug');

        if (static::$rsyncCommand === null) {
            throw new \Dachande\T00nieBox\Exception\UninitializedException("Initialize Rsync Command before execution.");
        }

        // debug(static::$rsyncCommand->getCommand());
        // exit;

        if ($returnOutput === true) {
            $output = '';
            $command = static::$rsyncCommand->getCommand();

            if (($fp = popen($command, "r"))) {
                while (!feof($fp)) {
                    $output .= fread($fp, 1024);
                }
                fclose($fp);
            } else {
                throw new \InvalidArgumentException("Cannot execute command: '" .$command. "'");
            }

            return $output;
        } else {
            static::$rsyncCommand->execute(true);
        }
    }
}
