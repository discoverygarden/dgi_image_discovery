# DGI Image Discovery

## Introduction

A module to facilitate image discovery for Islandora repository items.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/Islandora/islandora/)

## Installation

Install as usual, see
[this](https://www.drupal.org/docs/extending-drupal/installing-modules) for
further information.

## Usage

This module allow for image discovery on parent aggregate objects such as
collections, compounds and paged objects in multiple context.

### Search API

Search API can be made to index URLs to the discovered image in multiple ways:

- `deferred`: Create URL to dedicated endpoint which can handle the final image lookup. Given responses here can be aware of Drupal's cache tag invalidations, we can accordingly change what is ultimately served.
- `pre_generated`: Creates URL to styled image directly. May cause stale references to stick in the index, due to changing access control constraints.

#### Deferral mechanism

There are multiple plugins to dereference deferred URLs:

- `redirect`: Issue a redirect to the final derived image destination from our endpoint. Easily enough done; however:
  - incurs another round trip
  - can cause a race condition if two items being displayed in a set of results happen to reference the same image. Drupal maintains a lock/semaphore around the image derivation: If the second request occurs while the first still has the lock for deriving the image, then the second request will receive an HTTP 503 with `Retry-After` of `3` seconds, but many browsers do not make use of the `Retry-After` header.
- `subrequest`: Perform subrequest to stream the image directly from our endpoint. Can deal with the 503 with `Retry-After`.

The plugin in use is presently controlled with the `DGI_IMAGE_DISCOVERY_DEFERRED_PLUGIN`, which defaults to `subrequest`.

### Views

Views referencing node content can directly make use of a virtual field.

When configuring a content view, add and configure the virtual field
"DGI Image Discovery Discovered Image".

## Troubleshooting/Issues

Having problems or solved a problem? Contact
[discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, create an issue, pull request
and/or contact
[discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
