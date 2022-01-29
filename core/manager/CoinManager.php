<?php
declare(strict_types=1);

namespace Manager;

class CoinManager
{
    public static function getAllHistory(string $coinStat): array
    {
        $coinArray = [];

        $coinTable = new \Database\Table('coin');

        $coinPairs = $coinTable->fetchPairs('id', 'symbol', ['ignore = ?' => 0]);

        foreach($coinPairs as $id => $symbol)
        {
            $coinArray[$symbol] = self::getCoinHistory($id, $coinStat);
        }

        return $coinArray;
    }


    public static function getCoinHistory(int $coinId, string $coinStat, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $coinTable = new \Database\Table('coin');
        $coinImportTable = new \Database\Table('coin_import');
        $coinHistoryTable = new \Database\Table('coin_history');

        $coinRow = $coinTable->select(['symbol'], ['id = ?' => $coinId]);

        if(!$coinRow)
        {
            throw new \Exception("Coin $coinId not found");
        }

        $dateNow = new \DateTime;

        if($dateStart)
        {
            $dateStart = \DateTime::createFromFormat('Y-m-d', $dateStart)->setTime(0,0);

            if(!$dateStart || $dateStart > $dateNow)
            {
                throw new \Exception("Invalid starting date");
            }
        }

        if($dateEnd)
        {
            $dateEnd = \DateTime::createFromFormat('Y-m-d', $dateEnd)->setTime(0,0);

            if(!$dateEnd || $dateEnd > $dateNow || ($dateStart && $dateEnd <= $dateStart))
            {
                throw new \Exception("Invalid ending date");
            }
        }

        $coinImportRow = $coinImportTable->select(['last_complete'], ['coin_id = ?' => $coinId]);

        $coinHistoryPairs = \Manager\CacheManager::load(strtolower($coinRow['symbol']) . '_' . $coinStat, $dateStart, $dateEnd);

        if(!$coinHistoryPairs || ($dateEnd ? new \DateTime(array_key_last($coinHistoryPairs)) < $dateEnd : array_key_last($coinHistoryPairs) !== $coinImportRow['last_complete']))
        {
            $coinHistoryPairs = $coinHistoryTable->fetchPairs('date', $coinStat, ['coin_id = ?' => $coinId]);

            ksort($coinHistoryPairs);

            \Manager\CacheManager::save(strtolower($coinRow['symbol']) . '_' . $coinStat, $coinHistoryPairs);

            $coinHistoryPairs = \Manager\CacheManager::load(strtolower($coinRow['symbol']) . '_' . $coinStat, $dateStart, $dateEnd);
        }

        if(!$coinHistoryPairs)
        {
            throw new \Exception("History for coin $coinId not found");
        }

        return $coinHistoryPairs;
    }


    public static function getCoin(int $coinId): array
    {
        $coinTable = new \Database\Table('coin');

        $coinRow = $coinTable->select(['name', 'api_name', 'symbol', 'image'], ['id = ?' => $coinId]);

        if(!$coinRow)
        {
            throw new \Exception("Coin $coinId not found");
        }

        return $coinRow;
    }
}
