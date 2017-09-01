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
    $response = $client->get('https://security.appspot.com/vsftpd/Changelog.txt');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

if (0 === preg_match_all('` v?([0-9](\.[0-9]){2}) `', $response->getBody()->getContents(), $matches)) {
    exit('Impossible to find versions.');
}
$versions = $matches[1];
usort($versions, 'version_compare');

// Generate files

$fs = new Filesystem();

$folder = getcwd() . DIRECTORY_SEPARATOR . 'vsftpd';
if (!$fs->exists($folder)) {
    $fs->mkdir($folder);
}

foreach ($versions as $version) {
    $content = <<<EOF
VSFTPD_VERSION="$version"

EOF;

    $fs->dumpFile(
        $folder . DIRECTORY_SEPARATOR . $version,
        $content
    );
    if (end($versions) === $version) {
        $fs->dumpFile(
            $folder . DIRECTORY_SEPARATOR . 'latest',
            $content
        );
    }
}
