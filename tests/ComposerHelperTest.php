<?php

namespace luya\composer\tests;

use luya\composer\ComposerHelper;

class ComposerHelperTest extends TestCase
{
    public function testParseDirectorySeperator()
    {
        $this->assertSame('bar/foo', ComposerHelper::parseDirectorySeperator('bar{{DS}}foo'));
    }
}
