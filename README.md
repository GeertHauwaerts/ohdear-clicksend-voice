## About

This app is a webhook endpoint for [Oh Dear!](https://ohdear.app) and uses [ClickSend](https://clicksend.com)
to call your engineers when your website is down.

[![Build Status](https://travis-ci.org/GeertHauwaerts/ohdear-clicksend-voice.svg?branch=master)](https://travis-ci.org/GeertHauwaerts/ohdear-clicksend-voice)

## Installation

The installation is very easy and straightforward:

  * Create a `.env` file with your settings.
  * Point your webserver to the `public/` directory.
  * Run `composer install` to install the dependencies.

```console
$ cp .env.default .env
$ composer install
```

## Development & Testing

To verify the integrity of the codebase you can run the PHP linter:

```console
$ composer install
$ composer phpcs
```

## Collaboration

The GitHub repository is used to keep track of all the bugs and feature
requests; I prefer to work uniquely via GitHib and Twitter.

If you have a patch to contribute:

  * Fork this repository on GitHub.
  * Create a feature branch for your set of patches.
  * Commit your changes to Git and push them to GitHub.
  * Submit a pull request.

Shout to [@GeertHauwaerts](https://twitter.com/GeertHauwaerts) on Twitter at
any time :)
