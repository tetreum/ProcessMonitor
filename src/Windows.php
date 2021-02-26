<?php

namespace ProcessMonitor;

/*
"Useful" documentation: https://docs.microsoft.com/en-us/windows/win32/cimwin32prov/win32-process
*/

class Windows extends PlatformHelper implements PlatformInterface
{

    private $wmi = null;

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
        $processes = $this->runCommand("SELECT * FROM Win32_Process WHERE ProcessId = " . $id);

        // i don't know any other way to get the first entry of a variant object
        foreach ($processes as $process) {
            return $this->parseProcessData($process);
        }

        return false;
    }

    /**
     * Check whether the process identified by ID exists.
     * @param int
     * @return bool
     */
    public function exists($id)
    {
        $processes = $this->runCommand("SELECT * FROM Win32_Process WHERE ProcessId = " . intval($id));

        return (is_array($processes) && count($processes) > 0);
    }

    /**
     * Kills this process
     */
    public function kill($pid)
    {
        throw new \Exception("Kill method not implemented for Windows");
    }

    /**
     * Gets all process childs
     *
     * @return array
     */
    public function getChilds($pid)
    {
        throw new \Exception("Kill method not implemented for Windows");
    }

    /**
     * Gets multiple processes by name, regex accepted
     * @param string $processName
     * @return array|bool
     */
    public function searchMultiple($processName)
    {
        $processes = $this->runCommand("SELECT * FROM Win32_Process WHERE Name LIKE '" . $processName . "'");
        $list = [];

        foreach ($processes as $process) {
            $list[] = $this->parseProcessData($process);
        }
        return $list;
    }

    /**
     * Executes a command
     * @param string $cmd
     * @return array
     */
    protected function runCommand($cmd)
    {
        $this->display("Executing command: " . $cmd);

        return $this->getWMI()->ExecQuery($cmd);
    }

    /**
     * Setups WMI
     * @return \COM
     */
    private function getWMI()
    {
        if (empty($this->wmi)) {
            $this->wmi = new \COM('winmgmts://');
        }
        return $this->wmi;
    }

    /**
     * Sets process data to standard format
     * @param object $process
     * @return Process
     */
    private function parseProcessData($process)
    {
        return new Process([
            "pid" => $process->ProcessId,
            "command" => $process->CommandLine,
            'user' => "",
            'cpu' => (float)0,
            'ram' => (float)0,
            'vsz' => (int)0,
            'rss' => (int)0,
            'tty' => 0,
            'stat' => 0,
            'start' => 0,
            'time' => 0,
            'defunct' => false,
            'driver' => $this
        ]);
    }
}
