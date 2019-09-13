<?php

namespace ProcessMonitor;

interface PlatformInterface
{
    public function get($pid);

    public function getChilds($pid);

    public function kill($pid);

    public function searchMultiple($processName);
}
