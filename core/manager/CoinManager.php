<?php
declare(strict_types=1);

namespace Manager;

class CoinManager
{
    public static function importList(): void
    {
        $coinTable = new \Database\Table('coin');
        $apiNamePairs = $coinTable->fetchPairs('id', 'api_name', []);

        $coinArray = \Manager\ApiManager::request(action: 'markets', params: ['vs_currency' => 'usd', 'order' => 'market_cap_desc', 'page' => 1, 'per_page' => 100]);

        foreach($coinArray as $coin)
        {
            if(!in_array($coin['id'], $apiNamePairs, true))
            {
                $coinInfo = \Manager\ApiManager::request(coin: $coin['id'], params: ['localization' => 'false']);

                $coinTable->insert([
                    'name' => $coinInfo['name'],
                    'api_name' => $coinInfo['id'],
                    'symbol' => $coinInfo['symbol'],
                    'image' => $coinInfo['image']['small']
                ]);
            }
        }
    }


    public static function importHistory(): void
    {
        $date = new \DateTime('today');

        $coinTable = new \Database\Table('coin');
        $coinImportTable = new \Database\Table('coin_import');

        $coinPairs =  $coinTable->fetchPairs('id', 'api_name', ['ignore = ?' => 0]);
        $coinImportPairs = $coinImportTable->fetchPairs('coin_id', 'last_complete');

        foreach($coinPairs as $id => $apiName)
        {
            if(empty($coinImportPairs[$id]) || (!empty($coinImportPairs[$id]) && new \DateTime($coinImportPairs[$id]) < $date))
            {
                self::updateCoinHistory($apiName);

                break;
            }
        }
    }


    public static function updateCoinHistory(string $apiName): void
    {
        $historyArray = [];

        $date = new \DateTime;

        $coinTable = new \Database\Table('coin');
        $coinHistoryTable = new \Database\Table('coin_history');
        $coinImportTable = new \Database\Table('coin_import');

        $coinRow = $coinTable->select(['id'], ['api_name = ?' => $apiName]);

        if(!$coinRow)
        {
            throw new \Exception("Coin $apiName not found");
        }

        $coinImportRow = $coinImportTable->select(['id'], ['coin_id = ?' => $coinRow['id']]);

        if(!$coinImportRow)
        {
            $coinImportTable->insert(['coin_id' => $coinRow['id']]);
        }

        $historyDatePairs = $coinHistoryTable->fetchPairs('id', 'date', ['coin_id = ?' => $coinRow['id']]);

        $historyInfo = \Manager\ApiManager::request($apiName, 'history', ['localization' => 'false', 'date' => $date->format('d-m-Y')]);
var_dump($historyInfo);
echo "<br>";
        if(!in_array($date->format('Y-m-d'), $historyDatePairs, true) && $historyInfo)
        {
            if(isset($historyInfo['market_data']))
            {
                $historyArray[] = [
                    'coin_id' => $coinRow['id'],
                    'date' => $date->format('Y-m-d'),
                    'price' => $historyInfo['market_data']['current_price']['usd'],
                    'mcap' => $historyInfo['market_data']['market_cap']['usd'],
                    'volume' => $historyInfo['market_data']['total_volume']['usd']
                ];
            }
            else
            {
                $coinImportTable->update(['date' => $date->format('Y-m-d')], ['coin_id = ?' => $coinRow['id']]);
            }
        }

        while(isset($historyInfo['market_data']))
        {
            $date->modify('-1 day');

            if(!in_array($date->format('Y-m-d'), $historyDatePairs, true))
            {
                $historyInfo = \Manager\ApiManager::request($apiName, 'history', ['localization' => 'false', 'date' => $date->format('d-m-Y')]);
var_dump($historyInfo);
echo "<br>";
                if($historyInfo)
                {
                    if(isset($historyInfo['market_data']))
                    {
                        $historyArray[] = [
                            'coin_id' => $coinRow['id'],
                            'date' => $date->format('Y-m-d'),
                            'price' => $historyInfo['market_data']['current_price']['usd'],
                            'mcap' => $historyInfo['market_data']['market_cap']['usd'],
                            'volume' => $historyInfo['market_data']['total_volume']['usd']
                        ];
                    }
                    else
                    {
                        $coinImportTable->update(['last_complete' => (new \DateTime)->format('Y-m-d')], ['coin_id = ?' => $coinRow['id']]);
                    }
                }
            }
        }

        $coinHistoryTable->insertMultiple($historyArray);
    }


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


    public static function getCoinHistory(int $coinId, string $coinStat): array
    {
        $coinTable = new \Database\Table('coin');
        $coinImportTable = new \Database\Table('coin_import');
        $coinHistoryTable = new \Database\Table('coin_history');

        $coinRow = $coinTable->select(['symbol'], ['id = ?' => $coinId]);

        if(!$coinRow)
        {
            throw new \Exception("Coin $coinId not found");
        }

        $coinImportRow = $coinImportTable->select(['last_complete'], ['coin_id = ?' => $coinId]);

        $coinHistoryPairs = \Manager\CacheManager::load(strtolower($coinRow['symbol']) . '_' . $coinStat);

        if(!$coinHistoryPairs || array_key_last($coinHistoryPairs) !== $coinImportRow['last_complete'])
        {
            $coinHistoryPairs = $coinHistoryTable->fetchPairs('date', 'price', ['coin_id = ?' => $coinId]);

            ksort($coinHistoryPairs);

            \Manager\CacheManager::save(strtolower($coinRow['symbol']) . '_' . $coinStat, $coinHistoryPairs);
        }

        return $coinHistoryPairs;
    }


    public static function getRow(int $coinId): array
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
