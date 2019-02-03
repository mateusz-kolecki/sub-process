<?php
include __DIR__ . '/../vendor/autoload.php';

use SubProcess\Process;

$process = new Process(function () {
    echo "Hello from child process!\n";
});

$process->start();
$exitStatus = $process->wait();

var_dump($exitStatus->code()); // int(0)
