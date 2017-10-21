<?php
<<<CONFIG
packages:
    - "guzzlehttp/guzzle: ^6.2"
    - "symfony/filesystem: ^3.2"
CONFIG;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;

// Configure HTTP Client

$headers = [];
if (false !== getenv('GITHUB_TOKEN')) {
    $headers['Authorization'] = sprintf('token %s', getenv('GITHUB_TOKEN'));
}
$client = new Client(
    [
        'headers' => $headers,
    ]
);

// Retrieve versions

try {
    $response = $client->get('https://api.github.com/repos/docker/docker-ce/git/refs/tags');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = array_filter(
    array_map(
        function ($version) {
            return preg_replace('`refs/tags/v([0-9.]+)-ce?`', '$1', $version['ref']);
        },
        json_decode($response->getBody()->getContents(), true)
    ),
    function ($version) {
        return 1 === preg_match('`^[0-9.]+$`', $version);
    }
);
usort($versions, 'version_compare');

// Generate files

$fs = new Filesystem();

foreach ($versions as $version) {
    $content = <<<EOF
DOCKER_CE_VERSION="$version"

EOF;

    $fs->dumpFile($version, $content);
    if (end($versions) === $version) {
        $fs->dumpFile('latest', $content);
    }
}