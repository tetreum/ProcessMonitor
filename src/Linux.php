<?php

namespace ProcessMonitor;

class Linux extends PlatformHelper implements PlatformInterface
{

    public function __construct($debug = false)
    {
        $this->debug = (bool)$debug;
    }

    /**
     * Gets process info by id
     * @param int|string $id (you can set 4 or 4|345|45)
     * @return Process
     */
    public function get($id)
    {
        $out = $this->runCommand("ps u -p $id");

        return $this->parseProcessData($out[0]);
    }

    /**
     * Check whether the process identified by ID exists.
     * @param int
     * @return bool
     */
    public function exists($id)
    {
        $out = $this->runCommand("ps u -p ". intval($id));
        return (is_array($out) && count($out)>0) ? true : false;
    }

    /**
     * Gets multiple processes by name, regex accepted
     * @param string $processName
     * @return array|bool
     */
    public function searchMultiple($processName)
    {
        $list = [];
        $grep = "grep ";

        if ($this->isRegex($processName)) {
            $grep .= "-E ";
        }

        $out = $this->runCommand("ps faux | $grep '$processName'");

        foreach ($out as $k => $line) {
            $list[] = $this->parseProcessData($line);
        }

        return $list;
    }

    /**
     * Kills this process
     */
    public function kill($pid)
    {
        $this->runCommand("kill " . $pid);
    }

    /**
     * Gets all process childs
     *
     * @return array
     */
    public function getChilds($pid)
    {
        $out = $this->runCommand("ps u --ppid " . $pid);
        $processList = array();

        foreach ($out as $k => $line) {
            $processList[] = $this->parseProcessData($line);
        }

        return $processList;
    }

    /**
     * Executes a command
     * @param string $cmd
     * @return array
     */
    protected function runCommand($cmd)
    {
        $this->display("Executing command: " . $cmd);

        exec($cmd, $out);

        if (strpos($cmd, "grep ") !== false) {
            $out = $this->removeGrep($out);
        } else if (strpos($cmd, "ps ") !== false) { // skip the first line
            $out = array_slice($out, 1);
        }

        return $out;
    }

    /**
     * Removes grep line from grep results
     * @param array $lines
     * @return array
     */
    private function removeGrep($lines)
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
    protected function parseProcessData($line)
    {
        $defunct = false;

        preg_match("/([A-Za-z0-9_\-.\+]*)\s*([0-9]*)\s*([0-9.]*)\s*([0-9.]*)\s*([0-9]*)\s*([0-9]*)\s*([A-Za-z0-9_\-.\?\/]*)\s*([A-Za-z_\-.\+\-]*)\s*([A-Za-z0-9\:]*)\s*([0-9\:]*)\s*(.*)/", $line, $matches);
        //echo $line;p($matches);
        if (strpos($line, "<defunct>") !== false) {
            $defunct = true;
        }
        return new Process(array(
            'user' => $matches[1],
            'pid' => (int)$matches[2],
            'cpu' => (float)$matches[3],
            'ram' => (float)$matches[4],
            'vsz' => (int)$matches[5],
            'rss' => (int)$matches[6],
            'tty' => $matches[7],
            'stat' => $matches[8],
            'start' => $matches[9],
            'time' => $matches[10],
            'command' => $matches[11],
            'defunct' => $defunct,
            'driver' => $this
        ));
    }

    /**
     * Checks if given string may be a regex
     * @param string $str
     * @return bool
     */
    private function isRegex($str)
    {
        preg_match("/([|%\/]+)/", $str, $matches);

        if (sizeof($matches) == 0) {
            return false;
        }

        return true;
    }
}
