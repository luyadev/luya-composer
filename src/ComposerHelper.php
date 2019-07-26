<?php

namespace luya\composer;

/**
 * Composer Helper Class.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.5
 */
class ComposerHelper
{
    /**
     * Replace a string with {{DS}} chars to the current operating system directory seperator.
     *
     * @param string $dir The name of the directory
     * @return string The parse string.
     */
    public static function parseDirectorySeperator($dir)
    {
        return str_replace([
            '{{DS}}'
        ], [
            DIRECTORY_SEPARATOR,
        ], $dir);
    }
}
