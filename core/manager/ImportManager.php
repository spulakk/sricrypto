<?php
declare(strict_types=1);

namespace Manager;

use function Couchbase\basicDecoderV1;

class ImportManager
{
    public static function importCoinList(): void
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


    public static function importCoinHistory(): void
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


    public static function importCoinDominance(): void
    {
        $coinHistoryTable = new \Database\Table('coin_history');

        $datePairs = $coinHistoryTable->fetchPairs('id', 'date', ['coin_id = ?' => 1, 'dominance IS ?' => 'NULL'], 'date ASC');

        $i = 0;

        foreach($datePairs as $date)
        {
            $coinHistoryPairs = $coinHistoryTable->fetchPairs('coin_id', 'mcap', ['date = ?' => $date]);
echo $date . '<br>';
            $totalMcap = 0;

            foreach($coinHistoryPairs as $mcap)
            {
                $totalMcap += $mcap;
            }

            foreach($coinHistoryPairs as $coinId => $mcap)
            {
                $coinHistoryTable->update(['dominance' => $mcap / $totalMcap * 100], ['coin_id = ?' => $coinId, 'date = ?' => $date]);
            }

            $i++;

            if($i === 50)
            {
                break;
            }
        }
    }


    private static function updateCoinHistory(string $apiName): void
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
}