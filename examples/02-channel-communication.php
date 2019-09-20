<?php
include __DIR__ . '/../vendor/autoload.php';

use SubProcess\Child;
use SubProcess\Process;

$process = new Process(function (Child $child) {
    $channel = $child->channel();

    $channel->send("Hello from child process!");
    $channel->send(["You", " can ", "send even arrays!"]);

    $object = new \stdClass();
    $object->inFact = "you can send any";
    $object->serialisable = ['value'];

    $channel->send($object);
});

$process->start();

while (!$process->channel()->eof()) {
    var_dump($process->channel()->read());
}

$exitStatus = $process->wait();
var_dump($exitStatus->code());
