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
        'http://archive.apache.org/dist/openoffice/',
        ['connect_timeout' => 1, 'delay' => 1000, 'read_timeout' => 1, 'timeout' => 1, 'verify' => false]
    );
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = \array_filter(
    \array_map(
        function ($version) {
            return \preg_replace('`/$`', '', \trim($version->textContent));
        },
        \iterator_to_array($crawler->filterXPath('//a'))
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
OPENOFFICE_LANG="en-US"
OPENOFFICE_PACKAGE="https://sourceforge.net/projects/openofficeorg.mirror/files/${latestVersion}/binaries/en-US/Apache_OpenOffice_${latestVersion}_Linux_x86-64_install-deb_en-US.tar.gz/download"
OPENOFFICE_VERSION="${latestVersion}"

EOF
);
