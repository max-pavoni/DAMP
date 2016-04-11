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
            'suggest'=> [
                'didYouMean'=> [
                    'text'=> $q,
                    'phrase'=> [
                        'field'=> 'did_you_mean'
                    ]
                ]
            ],
            'query'=> [
                'bool'=> [
                    'should'=> [
                        [
                            'match'=> [
                                'title'=> [
                                    'query'=> $q,
                                    'boost' => 0.3
                                ]
                            ]
                        ],
                        [
                            'match'=> [
                                'body'=> [
                                    'query'=> $q,
                                    'boost' => 0.2
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

    if(!isset($_GET['page']) || $_GET['page'] == 1) {
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
        arsort($people);
    }
}

?>


<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title> DAMP</title>
    <link rel="icon"
          type="image/png"
          href="res/favicon.png">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="js/prediction.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <style>
        div.item {
            vertical-align: top;
            display: inline-block;
            text-align: center;
            width: 120px;
        }
        img .avatar {
            width: 100px;
            height: 100px;
        }
        .caption {
            display: block;
        }
    </style>
</head>
<body>

<div class="logo-bar-container">
    <div class="logo" align="middle">
        <a href="index.php"><img src="res/logo.png" align="middle" width="320"></a>
        <br><br>
    </div>
    <div class="search-bar" align="middle">
        <div id="info">
            <p id="info_start"> Premi sul microfono per avviare una ricerca vocale. </p>
            <p id="info_speak_now">Parla ora</p>
        </div>
        <form id="search-form" action="index.php" method="get" autocomplete="off">

            <input type="text" name="q" id="search" value="<?php if(isset($q)) echo $q; ?>" placeholder="Cerca qualcosa...">
            <img onclick="startButton()" id="start_img" src="res/mic.gif" alt="Start" />

            <div>
                <input type="submit" value="Cerca">
                <div  id="#input-container"></div>
            </div>
        </form>
        <?php if(isset($query_res) && $query_res['hits']['total'] >= 1 && ($_GET['page'] == 1 || $_GET['page'] == null && $_GET['q'] != null))  {?>
            <div class="panel panel-default" style="display: inline-block; float: right; margin: 50px 100px; width: 400px;" >
                <div class="panel-body">
                    <div>
                        <h4><b>Persone correlate:</b></h4>
                        <hr>
                        <div>

                        <?php
                        $i = 0;
                        foreach ($people as $person => $value) {

                            if($i < 3) {
                                echo '<div class="item"><a href="index.php?q=' . $person . '"><img class="avatar" style="margin-bottom: 20px;" height="70" width="70" src="res/default-avatar-250x250.png"/></a>' . '<a href="index.php?q=' . $person . '"><span class="caption">' . $person . '</span></a></div>';
                                $i++;
                            }
                        }
                        ?>
                    </div>
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
        if(!empty($query_res['suggest']['didYouMean'][0]['options'][0]['text'])){
            echo '<div style="margin-bottom: 20px;"><h3>', 'Forse cercavi: ', '<a href="index.php?q=',trim($query_res['suggest']['didYouMean'][0]['options'][0]['text']) ,'">',$query_res['suggest']['didYouMean'][0]['options'][0]['text'], '</a>', ' ?</h3></div>';
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
                        <a href="<?php echo $r['_source']['path']; ?>"><?php if(strlen($r['_source']['title']) < 100)
                            echo $r['_source']['title'];
                            else
                            echo substr($r['_source']['title'], 0, 100);

                            ?></a>
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



    <div class="pages">
        <?php

        if(isset($query_res) && $query_res['hits']['total'] >= 1){

            $numPag = floor($query_res['hits']['total'] / 10);
            if($query_res['hits']['total']%10 > 0)
                $numPag++;
            ?>
            <nav>
                <ul class="pagination">
                    <li>
                        <?php
                        echo '<li><a href="index.php?q=', $q, '&page=', 1,'"><<</a></li> ';

                        ?>
                    </li>
                    <?php
                    if($_GET['page'] != 1)
                        echo '<li><a href="index.php?q=', $q, '&page=', $_GET['page']-1,'"><</a></li> ';
                    ?>
                    <?php


                    if($_GET['page'] == null || $_GET['page'] < 5)
                        $middle = 6;
                    else
                        $middle = $_GET['page'];

                    for ($i = $middle -5; $i <= min($middle + 4, $numPag); $i++){
                        if($i == $_GET['page'])
                            echo '<li class="active"><a href="index.php?q=', $q, '&page=', $i,'">', $i, '</a></li> ';
                        else
                            echo '<li><a href="index.php?q=', $q, '&page=', $i,'">', $i, '</a></li> ';

                    }
                    ?>
                    <?php
                    if($_GET['page'] != $i-1)
                        echo '<li><a href="index.php?q=', $q, '&page=', $_GET['page']+1,'">></a></li> ';
                    ?>
                    <li>
                        <?php
                        echo '<li><a href="index.php?q=', $q, '&page=', $numPag,'">>></a></li> ';

                        ?>
                    </li>
                </ul>
            </nav>
            <?php
        }
        ?>
    </div>




</div>

<script>

    showInfo('info_start');

    var final_transcript = '';
    var recognizing = false;


    if (!('webkitSpeechRecognition' in window)) {
        //Speech API not supported here…
    } else { //Let’s do some cool stuff :)
        var recognition = new webkitSpeechRecognition(); //That is the object that will manage our whole recognition process.
        recognition.continuous = false;   //Suitable for dictation.
        recognition.interimResults = false;  //If we want to start receiving results even if they are not final.
        //Define some more additional parameters for the recognition:
        recognition.lang = "it_IT";
        recognition.maxAlternatives = 1; //Since from our experience, the highest result is really the best...
    }

    recognition.onstart = function() {
        //Listening (capturing voice from audio input) started.
        //This is a good place to give the user visual feedback about that (i.e. flash a red light, etc.)
        recognizing = true;
        showInfo('info_speak_now');
        start_img.src = 'res/mic-animate.gif';
    };

    recognition.onend = function() {
        //Again – give the user feedback that you are not listening anymore. If you wish to achieve continuous recognition – you can write a script to start the recognizer again here.
        recognizing = false;
        start_img.src = 'res/mic.gif';
        showInfo('info_start');

    };

    recognition.onresult = function(e) {
        document.getElementById('search').value
            = e.results[0][0].transcript;
        recognition.stop();
        document.getElementById('search-form').submit();
    };

    function startButton(event) {
        if(recognizing) {
            recognition.stop();
        }
        else{
            recognition.start();
        }
        //start_img.src = 'res/mic-animate.gif'; //We change the image to a slashed until the user approves the browser to listen and recognition actually starts. Then – we’ll change the image to ‘mic on’.
    }

    function showInfo(s) {
        if (s) {
            for (var child = info.firstChild; child; child = child.nextSibling) {
                if (child.style) {
                    child.style.display = child.id == s ? 'inline' : 'none';
                }
            }
            info.style.visibility = 'visible';
        } else {
            info.style.visibility = 'hidden';
        }
    }


</script>
</body>
</html>