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
    $response = $client->get('https://api.github.com/repos/sgerrand/alpine-pkg-glibc/git/refs/tags');
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
        return 1 === \preg_match('`^[0-9.\-r]+$`', $version);
    }
);
\usort($versions, 'version_compare');

// Generate files

$latestVersion = \end($versions);

$fs = new Filesystem();
$fs->dumpFile(
  'latest',
<<<EOF
GLIBC_PACKAGE="https://github.com/sgerrand/alpine-pkg-glibc/releases/download/{$latestVersion}/glibc-{$latestVersion}.apk"
GLIBC_BIN_PACKAGE="https://github.com/sgerrand/alpine-pkg-glibc/releases/download/{$latestVersion}/glibc-bin-{$latestVersion}.apk"
GLIBC_REPOSITORY_KEY="https://alpine-pkgs.sgerrand.com/sgerrand.rsa.pub"
GLIBC_VERSION="{$latestVersion}"

EOF
);
