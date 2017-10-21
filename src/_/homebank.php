<?php
<<<CONFIG
packages:
    - "fabpot/goutte: ^3.2"
    - "symfony/filesystem: ^3.2"
CONFIG;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

// Configure HTTP Client

$client = new Client();

// Retrieve versions

try {
    $crawler = $client->request(
        'GET',
        'http://bazaar.launchpad.net/~mdoyen/homebank/main/changes',
        ['connect_timeout' => 1, 'delay' => 1000, 'read_timeout' => 1, 'timeout' => 1, 'verify' => false]
    );

    $versions = [];
    do {
        preg_match_all('`([0-9.]+) release`', $crawler->text(), $matches);
        $versions = array_merge($versions, array_unique($matches[1]));

        try {
            $crawler = $client->click($crawler->selectLink('Older')->link());
        } catch (\Exception $exception) {
            $crawler = null;
        }
    } while (null !== $crawler);
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$versions = array_filter(
    $versions,
    function ($tags) {
        return version_compare($tags, '5.0.0') >= 0;
    }
);
usort($versions, 'version_compare');

// Retrieve branches

$branches = [];
foreach ($versions as $version) {
    $explodedVersion = explode('.', $version);
    $explodedVersion[2] = 'x';

    $branches[$version] = implode('.', $explodedVersion);
}

// Retrieve revisions

$revisions = [];
foreach ($branches as $branch) {
    try {
        $crawler = $client->request('GET', sprintf('http://bazaar.launchpad.net/~mdoyen/homebank/%s/changes', $branch));

        do {
            $crawler->filterXPath('//tr[contains(@class, "revision_log")]')->each(
                function (Crawler $line) use ($branch, &$revisions) {
                    $revision = trim($line->children()->getNode(0)->textContent);

                    if (0 === preg_match('`([0-9.]+) `', $line->children()->getNode(2)->textContent, $match)) {
                        return;
                    }
                    $tag = trim($match[1]);

                    if (0 !== strpos($tag, substr($branch, 0, -2)) || isset($revisions[$tag])) {
                        return;
                    }

                    $revisions[$tag] = $revision;
                }
            );

            try {
                $crawler = $client->click($crawler->selectLink('Older')->link());
            } catch (\Exception $exception) {
                $crawler = null;
            }
        } while (null !== $crawler);
    } catch (\Exception $exception) {
        exit(sprintf('Impossible to retrieve revision number for branch "%s".', $branch));
    }
}

// Generate files

$fs = new Filesystem();

foreach ($versions as $version) {
    $content = <<<EOF
HOMEBANK_BRANCH="$branches[$version]"
HOMEBANK_REVISION="$revisions[$version]"
HOMEBANK_VERSION="$version"

EOF;

    $fs->dumpFile($version, $content);
    if (end($versions) === $version) {
        $fs->dumpFile('latest', $content);
    }
}
