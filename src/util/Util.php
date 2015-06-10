<?php

/**
 * Description of Util
 *
 * @author Rummenigge
 */
class Util {

    public static function startsWithString($haystack, $needle) {
        if (is_string($haystack) && is_string($needle)) {
            return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
        }
        return false;
    }

}
