<?php

require __DIR__ . '/../../../vendor/autoload.php';

// create elastic client
$host  = getenv('ELASTIC_HOST');
$index = getenv('ELASTIC_TEST_INDEX');
$type  = getenv('ELASTIC_TEST_TYPE');

$builder = \Elasticsearch\ClientBuilder::create();
$builder->setHosts([$host]);
$client = $builder->build();

\Bardex\Tests\AbstractTestCase::setClient($client, $index, $type);

// create test index
$params = [
    'index' => $index,
    'body'  => [
        'settings' => [
            'number_of_shards'   => 1,
            'number_of_replicas' => 1
        ]
    ]
];

$client->indices()->create($params);

$testdata = require __DIR__ . '/testdata.php';

foreach ($testData as $data) {
    $params = [
        'index' => $index,
        'type'  => $type,
        'id'    => $data['id'],
        'body'  => $data
    ];

    $client->index($params);
}