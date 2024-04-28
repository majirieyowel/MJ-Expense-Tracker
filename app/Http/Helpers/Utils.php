<?php

namespace App\Http\Helpers;


class Utils {

    public static function isValidUuid(string $string): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $string);
    }


}