<?php
/**
 * Created by PhpStorm.
 * User: alessandro
 * Date: 04/04/16
 * Time: 15.31
 */
require_once 'app/init.php';
if(isset($_GET['q'])){
    $q = trim($_GET['q']);
    $params = [
        'index' => 'people',
        'type' => 'page',
        'body' => [
            'query'=> [
                'bool'=> [
                    'should'=> [
                        [
                            'match'=> [
                                'title'=> [
                                    'query'=> $q,
                                    'boost'=> 1.5
                                ]
                            ]
                        ],
                        [
                            'match'=> [
                                'body'=> [
                                    'query' => $q,
                                ]
                            ]
                        ],
                        [
                            'match'=> [
                                'body'=> [
                                    'query' => $q,
                                    'fuzziness' => 'AUTO',
                                    'boost' => 0.5
                                ]
                            ]
                        ],
                        [
                            'match_phrase'=> [
                                'title'=> [
                                    'query'=> $q,
                                    'boost' => 3,
                                    'slop' => 3
                                ]
                            ]
                        ],
                        [
                            'match_phrase'=> [
                                'body'=> [
                                    'query'=> $q,
                                    'boost' => 2,
                                    'slop' => 3
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'highlight' => [
                'order'=> 'score',
                'pre_tags' => ['<b>'],
                'post_tags' => ['</b>'],
                'fields' => [
                    'body' => [
                        'type' => 'plain',
                        'fragment_size' => 150,
                        'number_of_fragments' => 3

                    ]
                ]
            ]
        ]
    ];
    $params['size'] = 10;
    if(isset($_GET['page'])){
        $page = $_GET['page'];

        $params['from'] = (($page-1)*(10)); // <-- will return second page
        #echo $params['from'];

    }
    $query_res = $client->search($params);
    #echo '<pre>', print_r($query), '</pre>';


    if($query_res['hits']['total'] >= 1){
        $output = $query_res['hits']['hits'];

    }

}


?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>DAMP</title>
    <link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>
<div class="logo-bar-container">
    <div class="logo" align="middle">
        <a href="index.php"><img src="res/logo.png" align="middle" width="320"></a>
    </div>
    <div class="search-bar" align="middle">
        <form action="index.php" method="get" autocomplete="off">
            <p> <label>
                    <input type="text" name="q" value="<?php if(isset($q)) echo $q; ?>" placeholder="Cerca qualcosa...">
                </label>
            </p>
            <div>
                <input type="submit" value="Cerca">
            </div>
        </form>
    </div>
</div>

<div class="results-container">
    <?php

    if(isset($q) || !trim($q)===''){
        if(isset($query_res) && $query_res['hits']['total'] >= 1) {
            echo '<div>', 'Trovati ', $query_res['hits']['total'], ' risultati in ', $query_res['took'], ' ms.', '</div>';
        }
        else {
            echo '<div>', 'Nessun risultato trovato', '</div>';
        }
    }
    ?>
    <?php
    if(isset($output)){
        foreach ($output as $r){
            ?>
            <div class="result">
                <div class="result-title">
                    <a href="<?php echo $r['_source']['path']; ?>" target="_blank"><?php echo $r['_source']['title']; ?></a>
                </div>
                <div class="result-text">
                    <?php
                    foreach ($r['highlight']['body'] as $fragment){
                        echo $fragment, ' ... ';
                    }
                    ?>
                </div>
                <div class="result-link">
                    <?php echo $r['_source']['path']; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <div class="pages" >
        <?php

        if(isset($query_res) && $query_res['hits']['total'] >= 1){

            $numPag = floor($query_res['hits']['total'] / 10);
            if($query_res['hits']['total']%10 > 0)
                $numPag++;

            for ($i = 1; $i <= $numPag; $i++){

                echo '<a href="index.php?q=', $q, '&page=', $i,'">', $i, '</a>  ';

            }
        }
        ?>
    </div>
</div>
</body>
</html>