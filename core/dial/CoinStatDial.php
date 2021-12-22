<?php
declare(strict_types=1);

namespace Dial;

class CoinStatDial
{
    public const P = 'price';

    public const M = 'mcap';

    public const V = 'volume';


    public static function translate(string $string): ?string
    {
        switch($string)
        {
            case self::P:
                return 'Price';
            case self::M:
                return 'Market cap';
            case self::V:
                return 'Trading volume';
            default:
                return null;
        }
    }
}