# README

Retrieve softwares versions

⚠️ This project is no longer maintained. ⚠️

## Usage

Run the script `retrieve-versions` to retrieve and dump all information into folder `generated`:

```sh
bin/retrieve-versions
```

Retrieved information can be used in your shell scripts:

```sh
# Use local usage

export $(xargs < generated/tianon/gosu/latest)

curl --location --output /usr/local/sbin/gosu "${GOSU_RELEASE}"
chmod +x /usr/local/sbin/gosu

# Use remote usage

export $(curl --location "https://gitlab.com/timonier/version-lister/raw/generated/tianon/gosu/latest" | xargs)

curl --location --output /usr/local/sbin/gosu "${GOSU_RELEASE}"
chmod +x /usr/local/sbin/gosu
```

## Links

* [melody](https://github.com/sensiolabs/melody)
