<?php

namespace ProcessMonitor;

class ProcessMonitor extends ProcessHelper
{
    public function __construct ($debug = false) {
        $this->debug = (bool)$debug;
    }

    /**
     * if debug mode is enabled, Prints the given line
     * @param string $line
     */
    protected function display ($line)
    {
        if (!$this->debug) {
            return;
        }
        parent::display($line);
    }



    /**
     * Checks if given string may be a regex
     * @param string $str
     * @return bool
     */
    private function isRegex ($str)
    {
        preg_match("/([|%\/]+)/", $str, $matches);

        if (sizeof($matches) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Gets process info by id
     * @param int|string $id (you can set 4 or 4|345|45)
     * @return Process
     */
    public function get ($id)
    {
        $out = $this->runCommand("ps u -p $id");

        return $this->parseProcessData($out[0]);
    }

    /**
     * Gets process info by name
     * @param string $processName
     * @return bool|Process
     */
    public function search ($processName)
    {
        $result = $this->searchMultiple($processName);

        if (!$result) {
            return false;
        }


        return $result[0];
    }

    /**
     * Gets multiple processes by name, regex accepted
     * @param string $processName
     * @param bool|false $appendSummary
     * @return array|bool|stdClass
     */
    public function searchMultiple ($processName, $appendSummary = false)
    {
        $higherCpu = 0;
        $defunct = false;
        $grep = "grep ";
        $list = array();

        if ($this->isRegex($processName)) {
            $grep .= "-E ";
        }

        $out = $this->runCommand("ps faux | $grep '$processName'");

        foreach ($out as $k => $line)
        {
            $proc = $this->parseProcessData($line);

            if (!$higherCpu || ($proc->cpu > $higherCpu->cpu)) {
                $higherCpu = $proc;
            }
            if ($proc->defunct) {
                $defunct = true;
                break;
            }
            $list[] = $proc;
        }

        if (sizeof($list) == 0) {
            return false;
        }


        if ($appendSummary) {
            $response = new stdClass();
            $response->processes = $list;
            $response->summary = array(
                'cpu' => $higherCpu,
                'defunct' => $defunct
            );
            return $response;
        } else {
            return $list;
        }
    }

}