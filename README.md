# Shifter - Local

[![CircleCI](https://circleci.com/gh/getshifter/shifter-local/tree/master.svg?style=svg)](https://circleci.com/gh/getshifter/shifter-local/tree/master)

[![shifter_local](http://dockeri.co/image/getshifter/shifter_local)](https://hub.docker.com/r/getshifter/shifter_local/)

Docker image for testing WordPress themes and plugins while migrating to Shifter. This image is also available on [Dockerhub](https://hub.docker.com/r/getshifter/shifter_local/).

## Limitations

Shifter-Local is released for the purpose of only checking the operation of WordPress theme and plug-in in Shifter platform.

The following features, combined with the Shifter platform. do not work on Shifter-Local.

- All Shifter menus in WordPress dashboard
  - Terminate, Generate, or etc...
- Passwordless login

## Requirements

- docker
- docker-compose

## Getting Started

```
git clone https://github.com/getshifter/shifter-local.git
```
```
cd shifter-local
```

```
docker-compose up -d
```

Visit [https://127.0.0.1:8443](https://127.0.0.1:8443) in your browser.

Use `Ctl + C` in your terminal window to stop running containers.

### Linux

Considering file permissions, we recommend using volumes on Linux.

```
docker-compose -f docker-compose.yml -f docker-compose_linux.yml up
```

### Updating Docker Image

```
docker-compose pull
```

### Change base PHP version

modify `docker-compose.yml` like below

```
-    image: getshifter/shifter_local:latest
+    image: getshifter/shifter_local:7.3
```

- avaliable tags => [getshifter/shifter_local Tags - Docker Hub](https://hub.docker.com/r/getshifter/shifter_local/tags)

### Storage

- MacOS
  - `./volume/app`: `wordpress/wp-content` files
  - `./volume/db`: `mysql` databases
- Linux (detect path using `docker volume inspect`)
  - `/var/lib/docker/volumes/shifterlocal_app/_data`: `wordpress/wp-content` files
  - `/var/lib/docker/volumes/shifterlocal_db/_data`: `mysql` databases

All data will be persisted in these directories, even if the Docker containers are stopped.

To start over with the installation, simply delete `./volume/` of `docker volume rm`.

## Debugging

Some plugins and themes may conflict with Shifter. Conflicts usually cause the Generator to hang or fail. Here are a few tips.

### Check for a valid JSON format

Your site should return a list of valid URLs in JSON format for Shifter to crawl for generating.

Add `?url=0` to the websites URL to test.

[https://127.0.0.1:8443?urls=0](`https://127.0.0.1:8443?urls=0`)

### Check for list of all target pages

If the number of pages to be generated is more than 100, you can check the target by 100 pages by increasing the number of the urls parameter. (e.g. urls=1, urls=2)

Pages that do not appear in this list are not subject to generate.

Common problems and solutions when there are no pages in the list are as follows.

- pagination issues. (Work in progress)

### Displaying PHP Errors

While running your site on Shifters [production](https://go.getshifter.io) environment PHP warning are suppressed. These warning if any are visible while running Shifter-Local for debugging your theme.

## Considerations

- Depending on your local environment mail functions may send from this container.  Example: Under `Outbound Port 25 Blocking`

## Disclaimer

This Docker image is similar to the one used by Shifter, but does not guarantee complete compatibility.
