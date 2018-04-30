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

// Retrieve tags

try {
    $response = $client->get('https://api.github.com/repos/bitly/oauth2_proxy/git/refs/tags');
} catch (\Exception $exception) {
    exit('Impossible to retrieve versions.');
}

$tags = \array_filter(
    \array_map(
        function ($version) {
            return \substr($version['ref'], 10);
        },
        \json_decode($response->getBody()->getContents(), true)
    ),
    function ($version) {
        return 1 === \preg_match('`^v[0-9.]+$`', $version);
    }
);
\usort($tags, 'version_compare');

// Retrieve versions

$versions = \array_filter(
    \array_map(
        function ($tag) use ($client) {
            try {
                $response = $client->get(\sprintf('https://api.github.com/repos/bitly/oauth2_proxy/releases/tags/%s', $tag));
            } catch (\Exception $exception) {
                return null;
            }

            $response = \json_decode($response->getBody()->getContents(), true);

            foreach ($response['assets'] as $asset) {
                if (false === \strpos($asset['browser_download_url'], 'linux-amd64')) {
                    continue;
                }

                \preg_match(
                    '`https://github.com/bitly/oauth2_proxy/releases/download/(v[0-9.]+)/google_auth_proxy-([0-9.]+).linux-amd64.go([0-9.]+).tar.gz`',
                    $asset['browser_download_url'],
                    $matches
                );
                if (!empty($matches)) {
                    return [
                        'GO_VERSION' => $matches[3],
                        'TAG' => $matches[1],
                        'VERSION' => $matches[2],
                    ];
                }

                \preg_match(
                    '`https://github.com/bitly/oauth2_proxy/releases/download/(v[0-9.]+)/oauth2_proxy-([0-9.]+).linux-amd64.go([0-9.]+).tar.gz`',
                    $asset['browser_download_url'],
                    $matches
                );
                if (!empty($matches)) {
                    return [
                        'GO_VERSION' => $matches[3],
                        'TAG' => $matches[1],
                        'VERSION' => $matches[2],
                    ];
                }

                return null;
            }

            return null;
        },
        $tags
    ),
    function ($version) {
        return null !== $version;
    }
);

// Generate files

$fs = new Filesystem();

foreach ($versions as $version) {
    $content = <<<EOF
OAUTH2_PROXY_GO_VERSION="${version['GO_VERSION']}"
OAUTH2_PROXY_TAG="${version['TAG']}"
OAUTH2_PROXY_VERSION="${version['VERSION']}"

EOF;

    $fs->dumpFile($version['VERSION'], $content);
    if (\end($versions) === $version) {
        $fs->dumpFile('latest', $content);
    }
}
