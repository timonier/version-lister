<?php
<<<CONFIG
packages:
    - "fabpot/goutte: ^3.2"
    - "symfony/filesystem: ^3.2"
CONFIG;

use Goutte\Client;
use Symfony\Component\Filesystem\Filesystem;

// Configure HTTP Client

$client = new Client();

// Retrieve versions

try {
    $crawler = $client->request('GET', 'http://archive.apache.org/dist/openoffice/');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = array_filter(
    array_map(
        function ($version) {
            return preg_replace('`/$`', '', trim($version->textContent));
        },
        iterator_to_array($crawler->filterXPath('//a'))
    ),
    function ($version) {
        return 1 === preg_match('`^[0-9.]+$`', $version);
    }
);
usort($versions, 'version_compare');

// Generate files

$fs = new Filesystem();

$folder = getcwd() . DIRECTORY_SEPARATOR . 'openoffice';
if (!$fs->exists($folder)) {
    $fs->mkdir($folder);
}

foreach ($versions as $version) {
    $content = <<<EOF
OPENOFFICE_VERSION="$version"

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
