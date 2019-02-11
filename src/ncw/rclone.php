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
    $response = $client->get('https://api.github.com/repos/ncw/rclone/git/refs/tags');
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
        $client->get("https://github.com/ncw/rclone/releases/download/v{$version}/rclone-v{$version}-linux-amd64.zip");
        $latestVersion = $version;
    } catch (\Exception $exception) {
    }
} while (null === $latestVersion && !empty($versions));

$fs = new Filesystem();
$fs->dumpFile(
  'latest',
<<<EOF
RCLONE_RELEASE="https://github.com/ncw/rclone/releases/download/v{$latestVersion}/rclone-v{$latestVersion}-linux-amd64.zip"
RCLONE_SOURCE="https://github.com/ncw/rclone/archive/v{$latestVersion}.tar.gz"
RCLONE_VERSION="{$latestVersion}"

EOF
);
