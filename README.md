# ProcessMonitor [![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](LICENSE) [![Issues](https://img.shields.io/github/issues/tetreum/ProcessMonitor.svg?style=flat)](https://github.com/tetreum/ProcessMonitor/issues)

Monitor & manage system processes in PHP.

#### Install

    composer require tetreum/process-monitor "1.*"


#### Examples

To see all available process commands & properties, check:
https://github.com/tetreum/ProcessMonitor/blob/master/src/Process.php

##### See process status

```php
use ProcessMonitor\ProcessMonitor;

$monitor = new ProcessMonitor();
$process = $monitor->search("apache");
    
if (!$process) {
    exit;
}
    echo "Apache (PID: " . $process->pid . ") is using " . $process->cpu . "% CPU and " . $process->ram . " RAM";
```
##### Kill a process

```php
use ProcessMonitor\ProcessMonitor;

$monitor = new ProcessMonitor();
$process = $monitor->search("rust-server");

if (!$process) {
    exit;
}
    
if ($process->defunct) {
    $process->kill();
    // or you can just kill it's childs
    // $process->killChilds();
}
```

##### Search muliple processes at the same time

```php
use ProcessMonitor\ProcessMonitor;

$monitor = new ProcessMonitor();
$processList = $monitor->searchMultiple("rust-server|nginx");
```

You can also get a summary of the top consuming processes of this search

```php
use ProcessMonitor\ProcessMonitor;

$monitor = new ProcessMonitor();
$result = $monitor->searchMultiple("rust-server|nginx", true);

// $result->processes contains the process list
// $result->summary:
// $result->summary["cpu"] // the most cpu consuming process
// $result->summary["defunct"] // returns any defunct process
/*
    [summary] => Array
        (
            [cpu] => ProcessMonitor\Process Object
                (
                    [user] => root
                    [pid] => 1230
                    [cpu] => 5
                    [ram] => 0.1
                    [vsz] => 15624
                    [rss] => 1412
                    [tty] => ?
                    [stat] => Ss
                    [start] => Apr13
                    [time] => 0:00
                    [command] => nginx: master process /usr/sbin/nginx
                    [defunct] =>
                    [debug:protected] =>
                )

            [defunct] =>
        )
*/
```
