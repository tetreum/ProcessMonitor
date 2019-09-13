<?php

namespace ProcessMonitor;

class PlatformHelper
{
    protected $debug = false;

    /**
     * Returns if current enviroment is CLI
     *
     * @return bool
     */
    protected function isCLI()
    {
        return (php_sapi_name() === 'cli');
    }

    /**
     * Returns if current os is Windows
     * @return bool
     */
    protected function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * if debug mode is enabled, prints the given line
     * @param string $line
     */
    protected function display($line)
    {
        if (!$this->debug) {
            return;
        }

        if ($this->isCLI()) {
            $break = "\n";
        } else {
            $break = "<br>";
        }

        echo $line . $break;
    }
}
