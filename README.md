# README

Retrieve softwares versions

## Usage

Run the script `generate-all` to retrieve and dump all information into folder `generated`:

```sh
bin/generate-all
```

Retrieved information can be used in your shell scripts:

```sh
# Use local usage

export $(xargs < generated/tianon/gosu/latest)

curl --location --output /usr/local/sbin/gosu "${GOSU_RELEASE}"
chmod +x /usr/local/sbin/gosu

# Use remote usage

export $(curl --location "https://github.com/timonier/version-lister/raw/generated/tianon/gosu/latest" | xargs)

curl --location --output /usr/local/sbin/gosu "${GOSU_RELEASE}"
chmod +x /usr/local/sbin/gosu
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
