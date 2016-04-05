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
            'query' => [
                'bool' => [
                    'must' => [
                        #[ 'match' => [ 'title' => $q ] ],
                        [ 'match' => [ 'body' => $q ] ]
                    ]
                ]
            ]
        ]
    ];
    $params['size'] = 10;
    $params['from'] = 0; // <-- will return second page
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
                    <input type="text" name="q" placeholder="Cerca qualcosa...">
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
        if($query_res['hits']['total'] >= 1) {
            echo '<div>', '<b>Trovati ', $query_res['hits']['total'], ' risultati in ', $query_res['took'], ' ms.</b>', '</div>';
        }
        else {
            echo '<div>', '<b>Nessun risultato trovato</b>', '</div>';
        }
    }
    ?>
    <?php
    if(isset($output)){
        foreach ($output as $r){
            ?>
            <div class="result">
                <div class="result-title">
                    <a href="<?php echo str_replace('/home/alessandro','http://localhost:80',$r['_source']['path']); ?>" target="_blank"><?php echo $r['_source']['title']; ?></a>
                </div>
                <div class="result-text">
                    <?php $posizione = stripos($r['_source']['body'], $q);
                    if($posizione > 150)
                        echo "...".preg_replace("/".$q."/i", "<b>\$0</b>", substr($r['_source']['body'], $posizione -150, 300))."...";

                    else
                        echo "...".preg_replace("/".$q."/i", "<b>\$0</b>", substr($r['_source']['body'], 0, 300))."...";

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

        ?>
    </div>
</div>
</body>
</html>