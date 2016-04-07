<?php
/**
 * Created by PhpStorm.
 * User: alessandro
 * Date: 04/04/16
 * Time: 15.31
 */
require_once 'init.php';




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
                                    'boost' => 0.3,
                                    'cutoff_frequency' => 0.001,
                                    'fuzziness' => 1
                                ]
                            ]
                        ],
                        [
                            'match'=> [
                                'body'=> [
                                    'query'=> $q,
                                    'boost' => 0.2,
                                    'cutoff_frequency' => 0.001,
                                    'fuzziness' => 1
                                ]
                            ]
                        ],
                        [
                            'match_phrase'=> [
                                'title'=> [
                                    'query'=> $q,
                                    'boost' => 3,
                                    'slop' => 30
                                ]
                            ]
                        ],
                        [
                            'match_phrase'=> [
                                'body'=> [
                                    'query'=> $q,
                                    'boost' => 2,
                                    'slop' => 30
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

$output = null;
    if($query_res['hits']['total'] >= 1){
        $output = $query_res['hits']['hits'];

    }


    $people = array();


    if(!isset($_GET['page']) || $_GET['page'] > 1) {
        foreach ($output as $r) {

            preg_match('/people\/[\s\S]+\//', $r['_source']['path'], $matches);

            preg_match('/\/[\s\S]+\//', $matches[0], $nome);
            $nome[0] = str_replace('/', "", $nome[0]);
            $nome[0] = ucwords(strtolower($nome[0]));

            if (!isset($people[$nome[0]]))
                $people[$nome[0]] = 1;
            else
                $people[$nome[0]] = $people[$nome[0]] + 1;

        }
        ksort($people);
    }
}

?>


<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>DAMP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="js/prediction.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
</head>
<body>

<div class="logo-bar-container">
    <div class="logo" align="middle">
        <a href="index.php"><img src="res/logo.png" align="middle" width="320"></a>
    </div>
    <div class="search-bar" align="middle">
        <form id="search-form" action="index.php" method="get" autocomplete="off">
            <p> <label>
                    <input type="text" name="q" id="search" value="<?php if(isset($q)) echo $q; ?>" placeholder="Cerca qualcosa...">
                </label>
            </p>

            <div>
                <input type="submit" value="Cerca">
                <div  id="#input-container"></div>
            </div>
        </form>
        <?php if(isset($query_res) && $query_res['hits']['total'] >= 1 && ($_GET['page'] == 1 || $_GET['page'] == null && $_GET['q'] != null))  {?>
            <div class="panel panel-default" style="display: inline-block; float: right; margin: 0px 150px; width: 300px;" >
                <div class="panel-body">
                    <div>
                        <p><b>Ricerche correlate:</b></p>
                        <hr>

                        <?php foreach ($people as $person => $value)
                            echo '<div style="margin-top: 10px;">'. '<a href="index.php?q='. $person .'">'.$person.'</a></div>'
                        ?>
                    </div>

                </div>
            </div>
        <?php }
        ?>
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

    <div id="results" style="display: inline-block; width: 50%">
    <?php
    if(isset($output)){
        foreach ($output as $r){
            ?>
            <div class="result">
                <div class="result-title">
                    <a href="http://<?php echo $r['_source']['path']; ?>"><?php echo $r['_source']['title']; ?></a>
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
    </div>





    <div class="pages"></div>
    <?php

    if(isset($query_res) && $query_res['hits']['total'] >= 1){

        $numPag = floor($query_res['hits']['total'] / 10);
        if($query_res['hits']['total']%10 > 0)
            $numPag++;
        echo '<ul class="pagination-ul">';
        echo '<li class="pagination-li" style="float:left"><a class="active">PAGE</a>';

        for ($i = 1; $i <= $numPag; $i++){
            echo '<li class="pagination-li" ><a href="index.php?q=', $q, '&page=', $i,'">', $i, '</a></li> ';

        }
        echo '</ul>';
    }
    ?>




</div>


</body>
</html>