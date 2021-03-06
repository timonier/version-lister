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
    $crawler = $client->request(
        'GET',
        'https://sourceforge.net/projects/extract-xiso/files/extract-xiso%20source/',
        ['connect_timeout' => 1, 'delay' => 1000, 'read_timeout' => 1, 'timeout' => 1, 'verify' => false]
    );
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = \array_filter(
    \array_map(
        function ($version) {
            return \preg_replace('`extract-xiso-([0-9.]+).tar.gz`', '$1', \trim($version->textContent));
        },
        \iterator_to_array($crawler->filterXPath('//span[contains(@class, "name")]'))
    ),
    function ($version) {
        return \version_compare($version, '2.7.0') >= 0;
    }
);
\usort($versions, 'version_compare');

// Generate files

$latestVersion = \end($versions);

$fs = new Filesystem();
$fs->dumpFile(
  'latest',
<<<EOF
EXTRACT_XISO_SOURCE="https://sourceforge.net/projects/extract-xiso/files/extract-xiso%20source/extract-xiso-{$latestVersion}.tar.gz/download"
EXTRACT_XISO_VERSION="{$latestVersion}"

EOF
);
