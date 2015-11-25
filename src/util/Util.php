<?php

namespace util;
/**
 * Description of Util
 *
 * @author Rummenigge
 */
class Util {

    public static function startsWithString($haystack, $needle) {
        if (is_string($haystack) && is_string($needle)) {
            if (strlen($haystack) <= 0) {
                return false;
            }
            return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
        }
        return false;
    }
}
