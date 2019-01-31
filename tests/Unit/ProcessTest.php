<?php

namespace SubProcess\Unit;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SubProcess\Channel;
use SubProcess\Process;

class ProcessTest extends TestCase
{
    /** @test */
    public function whenIteratorIsReturnedThenAllElementAreSendOverChannel()
    {
        $process = new Process(function () {
            // In PHP >= 5.5.0 you can use Generator which is also Iterator
            /*
            yield 'Hello From Child';

            for ($count = 0; $count < 3; $count++) {
                yield $count;
            }
            */
            return new ArrayIterator(array('Hello From Child', 0, 1, 2));
        });

        $process->start();

        $messages = [];
        while ($process->channel()->eof()) {
            list(, $messages[]) = $process->channel()->read();
        }

        $this->assertEquals(array("Hello From Child", 0, 1, 2), $messages);
    }
}
