<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    public function testBasicAssertions()
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(1, 1);
    }

    public function testArrayOperations()
    {
        $array = [1, 2, 3];
        $this->assertCount(3, $array);
        $this->assertContains(2, $array);
    }

    public function testStringOperations()
    {
        $string = "Hello World";
        $this->assertEquals("Hello World", $string);
        $this->assertStringContainsString("Hello", $string);
    }
}