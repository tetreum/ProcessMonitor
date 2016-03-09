# ProcessMonitor

Monitor & manage system processes in PHP.

#### Install

    composer require tetreum/process-monitor "1.*"


#### Examples

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