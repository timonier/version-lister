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
if (false !== \getenv('GITHUB_TOKEN')) {
    $headers['Authorization'] = \sprintf('token %s', \getenv('GITHUB_TOKEN'));
}
$client = new Client(
    [
        'headers' => $headers,
    ]
);

// Retrieve versions

try {
    $response = $client->get('https://api.github.com/repos/restic/rest-server/git/refs/tags');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = \array_filter(
    \array_map(
        function ($version) {
            return \preg_replace('`refs/tags/(release-|v)?`', '', $version['ref']);
        },
        \json_decode($response->getBody()->getContents(), true)
    ),
    function ($version) {
        return 1 === \preg_match('`^[0-9.]+$`', $version);
    }
);
\usort($versions, 'version_compare');

// Generate files

$latestVersion = null;
do {
    $version = \array_pop($versions);

    try {
        $client->get("https://github.com/restic/rest-server/releases/download/v{$version}/rest-server-{$version}-linux-amd64.gz");
        $latestVersion = $version;
    } catch (\Exception $exception) {
    }
} while (null === $latestVersion && !empty($versions));

$fs = new Filesystem();
$fs->dumpFile(
  'latest',
<<<EOF
REST_SERVER_RELEASE="https://github.com/restic/rest-server/releases/download/v{$latestVersion}/rest-server-{$latestVersion}-linux-amd64.gz"
REST_SERVER_SOURCE="https://github.com/restic/rest-server/archive/v{$latestVersion}.tar.gz"
REST_SERVER_VERSION="{$latestVersion}"

EOF
);
