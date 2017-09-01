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
    $crawler = $client->request('GET', 'https://sourceforge.net/projects/davmail/files/davmail/');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = array_filter(
    array_map(
        function ($version) {
            return trim($version->textContent);
        },
        iterator_to_array($crawler->filterXPath('//a[contains(@class, "name")]'))
    ),
    function ($version) {
        return 1 === preg_match('`^[0-9.]+$`', $version) && version_compare($version, '4.0') >= 0;
    }
);
usort($versions, 'version_compare');

// Retrieve builds

$builds = [];
foreach ($versions as $version) {
    try {
        $crawler = $client->request(
            'GET',
            sprintf('https://sourceforge.net/projects/davmail/files/davmail/%s/', $version)
        );
    } catch (\Exception $exception) {
        exit(sprintf('Impossible to retrieve build number of version "%s".', $version));
    }

    if (0 === preg_match(sprintf('`davmail-linux-x86_64-%s-([0-9]+).tgz`', preg_quote($version)), $crawler->text(), $matches)) {
        exit(sprintf('Impossible to find build number of version "%s".', $version));
    }
    $builds[$version] = $matches[1];
}

// Generate files

$fs = new Filesystem();

$folder = getcwd() . DIRECTORY_SEPARATOR . 'davmail';
if (!$fs->exists($folder)) {
    $fs->mkdir($folder);
}

foreach ($versions as $version) {
    $content = <<<EOF
DAVMAIL_BUILD="$builds[$version]"
DAVMAIL_VERSION="$version"

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
