<?php
/**
 * Created by PhpStorm.
 * User: alessandro
 * Date: 04/04/16
 * Time: 15.06
 */

require_once 'vendor/autoload.php';
use Elasticsearch\ClientBuilder;

#$es = new Elasticsearch\Client(['hosts' => ['127.0.0.1:9200']]);

$hosts = [
    '127.0.0.1:9200'                        // IP + Port
];
$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
->setHosts($hosts)                          // Set the hosts
->build();                                  // Build the client object
