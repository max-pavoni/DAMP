<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 06/04/16
 * Time: 18:23
 */
require_once '../init.php';

if(isset($_GET['q'])) {

    $q = $_GET['q'];

    $params = [
        'index' => 'people',
        'body' => [
            'size'=> 0,
            'aggs'=> [
                'autocomplete'=> [
                    'terms'=> [
                        'field'=> 'autocomplete',
                        'order'=> [
                            '_count'=> 'desc'
                        ],
                        'include'=> [
                            'pattern'=> $q . '.*'
                        ]
                    ]
                ]
            ],
            'query'=> [
                'prefix'=> [
                    'autocomplete'=> [
                        'value'=> $q
                    ]
                ]
            ]
        ]
    ];


    function clean($string) {
        // Replaces all spaces with hyphens.
        $string = str_replace('\t', '', $string);

        $string = preg_replace('/[^A-Za-z0-9\s\à\è\é\ì\ò\ù]/', '', $string);

        return preg_replace('/[\s]+/', ' ', $string);
    }

    $results = $client->search($params);

    $opzioni = array();
    foreach($results['aggregations']['autocomplete']['buckets'] as $option) {


        array_push($opzioni, clean($option['key']));


    }

    
    echo json_encode($opzioni);
}