<?php

namespace Drupal\currencies_list\Controllers;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Element\Table;

class CurrenciesListAdminController extends ControllerBase
{
    public function list()
    {
        $database = \Drupal::database();
        $list = $database->query('SELECT * FROM currencies_list')->fetchAll();
        $headers = [
            ['data' => 'From currency'],
            ['data' => 'To currency'],
            ['data' => 'Exchange rate'],
        ];
        $rows = [];
        if (count($list) === 0) {
            $rows[] = [
                [
                    'colspan' => 3,
                    'data' => t('No saved rates'),
                ]
            ];
        } else {
            foreach ($list as $item) {
                $rows[] = [
                    'data' => [
                        [
                            'data' => "{$item->from_currency_name} ({$item->from_currency_code})",
                        ],
                        [
                            'data' => "{$item->to_currency_name} ({$item->to_currency_code})",
                        ],
                        [
                            'data' => "{$item->exchange_rate}",
                        ],
                    ]
                ];
            }
        }

        $build['table'] = [
            '#type' => 'table',
            '#header' => $headers,
            '#rows' => $rows,
        ];

        return $build;
    }
}