<?php
namespace Dachande\T00nieBox;

use AFM\Rsync\Rsync as BaseRsync;
use AFM\Rsync\Command as RsyncCommand;

class Rsync
{
    /**
     * Initialize Rsync
     *
     * This method does a full rsync command initialization. If you set $withTarget to false
     * the target will be omitted from the rsync command which can be use for just listing
     * remote files instead of synchronizing them.
     *
     * This will not execute the rsync command. To execute the command use the execute() method on
     * the returned object.
     *
     * @param  boolean $withTarget
     * @return \AFM\Rsync\Command
     */
    protected function initialize($withTarget = true)
    {
        // Set Rsync source
        $source = Configure::read('Rsync.source');
        $sourceUsername = Configure::read('Rsync.sourceUsername');
        if ($sourceUsername !== null) {
            $source = $sourceUsername . '@' . $source;
        }

        // Set Rsync target
        if ($withTarget === true) {
            $target = Configure::read('Rsync.target');
            $targetUsername = Configure::read('Rsync.targetUsername');
            if ($targetUsername !== null) {
                $target = $targetUsername . '@' . $target;
            }
        }

        // Initialize rsync
        $rsync = new BaseRsync();
        if ($withTarget === true) {
            $rsyncCommand = $rsync->getCommand($source, $target);
        } else {
            $rsyncCommand = $rsync->getCommand($source, '');
        }

        // Add options
        $options = Configure::read('Rsync.config.options');
        if ($options !== null && is_array($options)) {
            foreach ($options as $option) {
                $rsyncCommand->addOption($option);
            }
        }

        // Add arguments
        $arguments = Configure::read('Rsync.config.arguments');
        if ($arguments !== null && is_array($arguments)) {
            foreach ($arguments as $argument => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $rsyncCommand->addArgument($argument, $subValue);
                    }
                } else {
                    $rsyncCommand->addArgument($argument, $value);
                }
            }
        }

        return $rsyncCommand;
    }

    /**
     * Execute the rsync command and return its output
     *
     * As the rsync command objects execute() method just prints out the result of the
     * rsync command, this method is used to address this issue. Instead of just printing
     * out the result, it is stored in a variable and returned in the end.
     *
     * @param  \AFM\Rsync\Command $rsyncCommand
     * @return string
     */
    protected function execute(RsyncCommand $rsyncCommand)
    {
        $output = '';
        $command = $rsyncCommand->getCommand();

        if (($fp = popen($command, "r"))) {
            while (!feof($fp)) {
                $output .= fread($fp, 1024);
            }
            fclose($fp);
        } else {
            throw new \InvalidArgumentException("Cannot execute command: '" .$command. "'");
        }

        return $output;
    }
}
