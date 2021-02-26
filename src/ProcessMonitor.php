<?php

namespace ProcessMonitor;

class ProcessMonitor extends PlatformHelper
{
    private $driver;

    public function __construct($debug = false)
    {
        $this->debug = (bool)$debug;

        if ($this->isWindows()) {
            $this->driver = new Windows($this->debug);
        } else {
            $this->driver = new Linux($this->debug);
        }
    }

    /**
     * Gets process info by id
     * @param int|string $id (you can set 4 or 4|345|45)
     * @return Process|false
     */
    public function get($id)
    {
        if($this->exists($id)){
            return $this->driver->get($id);
        }
        else{
            return false;
        }
    }
    
    /**
     * Check whether the process identified by ID exists.
     * @param int
     * @return bool
     */
    public function exists($id)
    {
        return $this->driver->exists($id);
    }

    /**
     * Gets process info by name
     * @param string $processName
     * @return bool|Process
     */
    public function search($processName)
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
     * @return array|bool|\stdClass
     */
    public function searchMultiple($processName, $appendSummary = false)
    {
        $processes = $this->driver->searchMultiple($processName);

        if (sizeof($processes) == 0) {
            return false;
        }

        if ($appendSummary) {
            $higherCpu = null;
            $defunct = false;

            foreach ($processes as $proc) {
                if (!$higherCpu || ($proc->cpu > $higherCpu->cpu)) {
                    $higherCpu = $proc;
                }
                if ($proc->defunct) {
                    $defunct = true;
                    break;
                }
            }

            $response = new \stdClass();
            $response->processes = $processes;
            $response->summary = array(
                'cpu' => $higherCpu,
                'defunct' => $defunct
            );
            return $response;
        } else {
            return $processes;
        }
    }

}
