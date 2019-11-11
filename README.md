# README

Retrieve softwares versions

## Usage

Run the script `generate` to retrieve and dump all information into folder `generated`:

```sh
bin/generate
```

Retrieved information can be used in your shell scripts:

```sh
# Use local version

export $(xargs < generated/docker-compose/latest)

curl --location --output /usr/local/sbin/docker-compose "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-linux-x86_64"
chmod +x /usr/local/sbin/docker-compose

# Use remote version

export $(curl --location "https://github.com/timonier/version-lister/raw/generated/docker/compose/latest" | xargs)

curl --location --output /usr/local/sbin/docker-compose "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-linux-x86_64"
chmod +x /usr/local/sbin/docker-compose
```

## Contributing

1. Fork it.
2. Create your branch: `git checkout -b my-new-feature`.
3. Commit your changes: `git commit -am 'Add some feature'`.
4. Push to the branch: `git push origin my-new-feature`.
5. Submit a pull request.

If you like / use this project, please let me known by adding a [★](https://help.github.com/articles/about-stars/) on the [GitHub repository](https://github.com/timonier/version-lister).

## Links

* [melody](https://github.com/sensiolabs/melody)
