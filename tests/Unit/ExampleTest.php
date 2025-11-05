<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicAssertion()
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
    }

    public function testMathOperations()
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertEquals(1, 2 - 1);
        $this->assertEquals(6, 2 * 3);
    }

    public function testArrayOperations()
    {
        $array = [1, 2, 3];
        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
        $this->assertEquals([1, 2, 3], $array);
    }
}