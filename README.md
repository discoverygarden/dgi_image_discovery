# DGI Image Discovery

## Introduction

A module to facilitate image discovery for Islandora repository items. Image discovery looks for images in the following places and will use (and cache) the first one found:

* contents of a Media field, `field_representative_image` on the node
* an "Islandora thumbnail", i.e., a media that is "Media of" the node (using `field_media_of`) with a Media Use (`field_media_use`) taxonomy term with External URI (`field_external_uri`) equal to "http://pcdm.org/use#ThumbnailImage"
* a first child's Islandora thumbnail media, i.e. the Islandora thumbnail of the node with lowest weight (`field_weight`) that is a Member Of (`field_member_of`) the node in question. If not found on the first direct child, it will look at the first child's first child, and so forth to a depth of 3.


## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/Islandora/islandora/)

## Installation

Install as usual, see
[this](https://www.drupal.org/docs/extending-drupal/installing-modules) for
further information.

## Usage

This module allows for image discovery on parent aggregate objects such as
collections, compounds and paged objects in multiple context.

### Search API

Search API can be made to index URLs to the discovered image in multiple ways:

- `deferred`: Create URL to dedicated endpoint which can handle the final image lookup. Given responses here can be aware of Drupal's cache tag invalidations, we can accordingly change what is ultimately served.
- `pre_generated`: Creates URL to styled image directly. May cause stale references to stick in the index, due to changing access control constraints.

This is configurable on the field when it is added to be indexed. Effectively this defaults to `pre_generated` to maintain existing/current behaviour; however, `deferred` should possibly be preferred without other mechanisms to perform bulk reindexing due to changes on other entities. In particular, should there be something such as [Embargo](https://github.com/discoverygarden/embargo) and [Embargo Inheritance](https://github.com/discoverygarden/embargo_inheritance), where an access control statement applied to a parent node is expected to be applied to children. That said, `pre_generated` could be more convenient/efficient when there are no complex access control requirements in play.

#### Deferral mechanism

There are multiple plugins to dereference deferred URLs:

- `redirect`: Issue a redirect to the final derived image destination from our endpoint. Easily enough done; however:
  - incurs another round trip
  - can cause a race condition if two items being displayed in a set of results happen to reference the same image. Drupal maintains a lock/semaphore around the image derivation: If the second request occurs while the first still has the lock for deriving the image, then the second request will receive an HTTP 503 with `Retry-After` of `3` seconds, but many browsers do not make use of the `Retry-After` header.
- `subrequest`: Perform subrequest to stream the image directly from our endpoint. Can deal with the 503 with `Retry-After`.

The plugin in use is presently controlled with the `DGI_IMAGE_DISCOVERY_DEFERRED_PLUGIN`, which defaults to `subrequest`.

### Views

Views referencing node content can directly make use of a virtual field.

### Adding a "Representative Image" field to your content type

To override the use of the "Islandora" thumbnail, you can add a new field to each of your applicable content types. To do this:

1. In the "Manage fields" page for your content type, choose "Create a new field".
1. In the "Add a new field" list, choose "Media" (if on Drupal < 10.2, this is "Reference > Media")
1. Set the new field's label to "Representative image" so that the machine name of this field is `field_representative_image`. This machine name must be set; you can change the label later if you wish.
1. On the next page, in the "Type of item to reference" setting, choose "Media" and leave the "Allowed number of values" at 1.
1. On the next page, in the "Media type" checkboxes, choose "Image".
1. Click on "Save settings".

If you are adding the field to more than one content type, you should choose "Re-use an existing field" on subsequent content types.

### Adding a "Default Image" Field to Your Content Type ###
This module, through an update hook, will add a field_default_image field to the Islandora Models vocabulary.

- Configuring a Default Image for a Content Type
  - Create a media item of type Image to serve as the default image for the required content types.
  - In the Islandora Models vocabulary, select the appropriate default image for each term.

This setup allows the module to associate a default thumbnail (TN) with objects that lack a specific thumbnail for any given content type.

### Using the image in Views

When configuring a content view, add and configure the virtual field
"DGI Image Discovery Discovered Image".

### Using the image in Content Display

Under "Manage display" for a content type, you can enable the pseudo-field
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
