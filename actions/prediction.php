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

            'page' => [
                'text' => $q,

                'completion' => [
                    'field' => 'name_suggest',
                    'fuzzy' => [
                        'fuzziness' => 2
                    ]
                ]
            ]
        ]
    ];


    function clean($string) {
        // Replaces all spaces with hyphens.
        $string = str_replace('\t', '', $string);

        $string = preg_replace('/[^A-Za-z0-9\s]/', '', $string);

        return preg_replace('/[\s]+/', ' ', $string);
    }

    $results = $client->suggest($params);

    $opzioni = array();
    foreach($results['page'][0]['options'] as $option) {


        array_push($opzioni, clean($option['text']));


    }

    
    echo json_encode($opzioni);
}