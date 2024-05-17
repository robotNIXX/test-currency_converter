<?php

namespace Drupal\currencies_list\Services;

use Drupal;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CurrenciesApiService
{
    static function makeRequest(string $method, string $action, array $data = [])
    {
        try {
            $client = new Client([
                'base_uri' => \Drupal::config('currencies_list.currencies_list')
                    ->get('api_url'),
            ]);
            $data = array_merge($data, ['apikey' => \Drupal::config('currencies_list.currencies_list')->get('api_key')]);
            if ($method === 'POST') {
                $data = [
                    'body' => $data
                ];
            } else {
                $data = [
                    'query' => $data
                ];
            }
            $response = $client->request($method, $action, $data);

            return json_decode($response->getBody())->data;
        } catch (GuzzleException $e) {
            \Drupal::logger('currencies_list')->error($e->getMessage());
            \Drupal::messenger()->addError(t('Currencies: Cannot connect to the server. Please check the settings.'));
        }
    }

    static function updateRates()
    {
        $currencies =  \Drupal::config('currencies_list.currencies_list')->get('available_currencies');
        $available_currencies = implode(",", array_keys($currencies));
        $all_currencies = CurrenciesApiService::makeRequest('GET', 'currencies');
        $database = Drupal::database();
        $database->query("TRUNCATE {currencies_list}");
        foreach ($currencies as $currency) {
            $new_values = (array)self::makeRequest('GET', "latest", [
                'base_currency' => $currency,
                'currencies' => $available_currencies
            ]);
            foreach ($new_values as $key => $rate) {
                if ($currency !== $key) {
                    $from_currency_name = $all_currencies->$currency->name;
                    $to_currency_name = $all_currencies->$key->name;
                    $database->query("INSERT INTO {currencies_list} (from_currency_code, from_currency_name, to_currency_code, to_currency_name, exchange_rate) VALUES ('{$currency}', '{$from_currency_name}', '{$key}', '{$to_currency_name}', '{$rate}'   )");
                }
            }
        }
    }

    static function convert(float $amount, string $from_currency, string $to_currency) {
        $database = Drupal::database();
        $record = $database->query("SELECT `exchange_rate` FROM {currencies_list} where `from_currency_code` = '{$from_currency}' and `to_currency_code` = '{$to_currency}'")->fetch();
        if ($record) {
            return $record->exchange_rate * $amount;
        }
        Drupal::messenger()->addError(t('No any information for these currencies in out database. Please, change the request'));
    }
}