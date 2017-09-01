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
    $crawler = $client->request('GET', 'https://sourceforge.net/projects/extract-xiso/files/extract-xiso%20source/');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = array_filter(
    array_map(
        function ($version) {
            return preg_replace('`extract-xiso-([0-9.]+).tar.gz`', '$1', trim($version->textContent));
        },
        iterator_to_array($crawler->filterXPath('//a[contains(@class, "name")]'))
    ),
    function ($version) {
        return version_compare($version, '2.7.0') >= 0;
    }
);
usort($versions, 'version_compare');

// Generate files

$fs = new Filesystem();

$folder = getcwd() . DIRECTORY_SEPARATOR . 'extract-xiso';
if (!$fs->exists($folder)) {
    $fs->mkdir($folder);
}

foreach ($versions as $version) {
    $content = <<<EOF
EXTRACT_XISO_VERSION="$version"

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
