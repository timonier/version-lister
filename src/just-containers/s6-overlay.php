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
    $response = $client->get('https://api.github.com/repos/just-containers/s6-overlay/git/refs/tags');
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

$latestVersion = \end($versions);

$fs = new Filesystem();
$fs->dumpFile(
  'latest',
<<<EOF
S6_OVERLAY_RELEASE="https://github.com/just-containers/s6-overlay/releases/download/v{$latestVersion}/s6-overlay-amd64.tar.gz"
S6_OVERLAY_SIGNATURE="https://github.com/just-containers/s6-overlay/releases/download/v{$latestVersion}/s6-overlay-amd64.tar.gz.sig"
S6_OVERLAY_SOURCE="https://github.com/just-containers/s6-overlay/archive/v{$latestVersion}.tar.gz"
S6_OVERLAY_VERSION="{$latestVersion}"

EOF
);