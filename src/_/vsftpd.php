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
        'https://security.appspot.com/vsftpd/Changelog.txt',
        ['connect_timeout' => 1, 'delay' => 1000, 'read_timeout' => 1, 'timeout' => 1, 'verify' => false]
    );
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

if (0 === \preg_match_all('` v?([0-9](\.[0-9]){2}) `', $response->getBody()->getContents(), $matches)) {
    exit('Impossible to find versions.');
}
$versions = $matches[1];
\usort($versions, 'version_compare');

// Generate files

$fs = new Filesystem();

foreach ($versions as $version) {
    $content = <<<EOF
VSFTPD_VERSION="$version"

EOF;

    $fs->dumpFile($version, $content);
    if (\end($versions) === $version) {
        $fs->dumpFile('latest', $content);
    }
}
