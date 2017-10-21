<?php
<<<CONFIG
packages:
    - "guzzlehttp/guzzle: ^6.2"
    - "symfony/filesystem: ^3.2"
CONFIG;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

// Configure HTTP Client

$client = new Client();

// Retrieve versions

try {
    $response = $client->request(
        'GET',
        'https://data.services.jetbrains.com/products/releases?code=IIC&type=release',
        ['connect_timeout' => 1, 'delay' => 1000, 'read_timeout' => 1, 'timeout' => 1, 'verify' => false]
    );
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$releases = array_filter(
    json_decode($response->getBody()->getContents(), true)['IIC'],
    function ($version) {
        return isset($version['downloads']);
    }
);
uasort(
    $releases,
    function ($releaseA, $releaseB) {
        return version_compare($releaseA['version'], $releaseB['version']);
    }
);

// Generate files

$fs = new Filesystem();

foreach ($releases as $release) {
    $content = <<<EOF
INTELLIJ_IDEA_BUILD="${release['build']}"
INTELLIJ_IDEA_VERSION="${release['version']}"

EOF;

    $fs->dumpFile($release['majorVersion'], $content);
    if (end($releases) === $release) {
        $fs->dumpFile('latest', $content);
    }
}
