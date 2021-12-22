<?php
declare(strict_types=1);

namespace Manager;

class ApiManager
{
    private static string $url = 'https://api.coingecko.com/api/v3/coins';


    public static function request(string $coin = null, string $action = null, array $params = null): ?array
    {
        $url = self::$url;

        if($coin)
        {
            $url .= '/' . $coin;
        }

        if($action)
        {
            $url .= '/' . $action;
        }

        if($params)
        {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response ? json_decode($response, true) : null;
    }
}
