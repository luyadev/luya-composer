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
     * Or if windows system is detected replace forward slashes with back slashes.
     *
     * @param string $dir The name of the directory
     * @return string The parse string.
     */
    public static function parseDirectorySeperator($dir)
    {
        // check if we are on a windows system
        if (DIRECTORY_SEPARATOR === '\\') {
            // replace forwarding slashes for linux with windows backslash
            return str_replace('/', '\\', $dir);
        }

        return str_replace([
            '{{DS}}'
        ], [
            DIRECTORY_SEPARATOR,
        ], $dir);
    }
}
