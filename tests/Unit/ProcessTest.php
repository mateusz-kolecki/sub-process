<?php

namespace SubProcess\Unit;

use PHPUnit\Framework\TestCase;
use SubProcess\Channel;
use SubProcess\Process;

class ProcessTest extends TestCase
{
    /** @test */
    public function whenGeneratorIsPassedThenYieldSendsMessage()
    {
        $process = new Process(function () {
            yield 'Hello From Child';

            for ($count = 0; $count < 3; $count++) {
                yield $count;
            }
        });

        $process->start();

        $messages = [];
        while ($process->channel()->eof()) {
            list(, $messages[]) = $process->channel()->read();
        }

        $this->assertEquals(["Hello From Child", 0, 1, 2], $messages);
    }
}
