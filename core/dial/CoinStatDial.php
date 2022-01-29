<?php
declare(strict_types=1);

namespace Dial;

class CoinStatDial
{
    public const P = 'price';

    public const M = 'mcap';

    public const V = 'volume';

    public const D = 'dominance';


    public static function translate(string $input): ?string
    {
        switch($input)
        {
            case self::P:
                return 'Price';
            case self::M:
                return 'Market cap';
            case self::V:
                return 'Trading volume';
            case self::D:
                return 'Dominance';
            default:
                return null;
        }
    }
}