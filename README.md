[![Build Status](https://travis-ci.org/mateusz-kolecki/sub-process.svg?branch=master)](https://travis-ci.org/mateusz-kolecki/sub-process)

# sub-process
PHP process forking made easy.

This library will help you to fork process and manage it state.

## Install

```bash
composer require mkolecki/sub-process
```

## Usage

## RealPcntl fork and communication

To fork to sub-process first create `SubProcess\Process` instance and then call `start()` method.

To controll what new `Process` will do, you have to pass `callable`.
That `callable` will receive `Process` instance which has `Channel` to `send()` and `read()` messages between parrent and child process.

Example `examples/02-channel-communication.php`:
```php
<?php
include __DIR__ . '/../vendor/autoload.php';

use SubProcess\Process;

$process = new Process(function (Process $child) {
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

```

```bash
$ php examples/02-channel-communication.php
```

Output:
```text
string(25) "Hello from child process!"
array(3) {
  [0]=>
  string(3) "You"
  [1]=>
  string(5) " can "
  [2]=>
  string(17) "send even arrays!"
}
object(stdClass)#5 (2) {
  ["inFact"]=>
  string(16) "you can send any"
  ["serialisable"]=>
  array(1) {
    [0]=>
    string(5) "value"
  }
}
int(0)
```
