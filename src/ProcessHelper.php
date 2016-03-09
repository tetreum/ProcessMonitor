<?php

namespace ProcessMonitor;

class ProcessHelper
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
     * if debug mode is enabled, Prints the given line
     * @param string $line
     */
    protected function display ($line)
    {

        if ($this->isCLI()) {
            $break = "\n";
        } else {
            $break = "<br>";
        }

        echo $line . $break;
    }

    /**
     * Executes a command
     * @param string $cmd
     * @return array
     */
    protected function runCommand ($cmd)
    {
        $this->display("Executing command: " . $cmd);

        exec($cmd, $out);

        if (strpos($cmd, "grep ") !== false) {
            $out = $this->removeGrep($out);
        }else if (strpos($cmd, "ps ") !== false) { // skip the first line
            $out = array_slice($out, 1);
        }

        return $out;
    }

    /**
     * Removes grep line from grep results
     * @param array $lines
     * @return array
     */
    private function removeGrep ($lines)
    {
        foreach ($lines as $k => $line) {
            if (strpos($line, " grep ") !== false) {
                unset($lines[$k]);
                continue;
            }
        }

        return array_values($lines);
    }

    /**
     * Processes a ps line
     * @param string $line
     * @return Process
     */
    protected function parseProcessData ($line)
    {
        $defunct = false;

        preg_match("/([A-Za-z0-9_\-.\+]*)\s*([0-9]*)\s*([0-9.]*)\s*([0-9.]*)\s*([0-9]*)\s*([0-9]*)\s*([A-Za-z0-9_\-.\?\/]*)\s*([A-Za-z_\-.\+\-]*)\s*([A-Za-z0-9\:]*)\s*([0-9\:]*)\s*(.*)/", $line, $matches);
        //echo $line;p($matches);
        if (strpos($line, "<defunct>") !== false) {
            $defunct = true;
        }
        return new Process(array(
            'user' => $matches[1],
            'pid' => (int) $matches[2],
            'cpu' => (float) $matches[3],
            'ram' => (float) $matches[4],
            'vsz' => (int) $matches[5],
            'rss' => (int) $matches[6],
            'tty' => $matches[7],
            'stat' => $matches[8],
            'start' => $matches[9],
            'time' => $matches[10],
            'command' => $matches[11],
            'defunct' => $defunct,
            'debug' => $this->debug
        ));
    }
}