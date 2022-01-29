<?php
declare(strict_types=1);

namespace Manager;

class ChartManager
{
    public static function baselineData(int $coinId, string $coinStat, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $historyArray = \Manager\CoinManager::getCoinHistory($coinId, $coinStat, $dateStart, $dateEnd);

        $baseline = current($historyArray);

        foreach($historyArray as $key => $value)
        {
            if($value === $baseline)
            {
                $historyArray[$key] = 0;
            }
            elseif(!$value)
            {
                unset($historyArray[$key]);
                //$historyArray[$key] = null;
            }
            else
            {
                $historyArray[$key] = $value / $baseline * 100 - 100;
            }
        }

        return $historyArray;
    }
}