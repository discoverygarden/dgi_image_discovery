# DGI Image Discovery

## Introduction

A module to facilitate image discovery for Islandora repository items. Image discovery looks for images in the following places and will use the first one found:

* contents of a Media field, `field_representative_image` on the node
* an "Islandora thumbnail", i.e., a media that is "Member of" the node (using `field_member_of`) with a Media Use (`field_media_use`) taxonomy term with External URI (`field_external_uri`) equal to "http://pcdm.org/use#ThumbnailImage"
* a first child's Islandora thumbnail media, i.e. the Islanodra thumbnail of the node with lowest weight (`field_weight`) that is a Member Of (`field_member_of`) the node in question. If not found on the first direct child, it will look at the first child's first child, and so forth to a depth of 3. 


## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/Islandora/islandora/)

## Installation

Install as usual, see
[this](https://www.drupal.org/docs/extending-drupal/installing-modules) for
further information.

## Usage

This module allow for image discovery on parent aggregate objects such as
collections, compounds and paged objects.

## Configuration

### Adding a "Representative Image" field to your content type

To override the use of the "Islandora" thumbnail, you can add a new field to each of your applicable content types. To do this:

1. In the "Manage fields" page for your content type, choose "Create a new field".
1. In the "Add a new field" list, choose "Media" (if on Drupal < 10.3, this is "Reference > Media")
1. Set the new field's label to "Representative image" so that the machine name of this field is `field_representative_image`. This machine name must be set; you can change the label later if you wish. 
1. On the next page, in the "Type of item to reference" setting, choose "Media" and leave the "Allowed number of values" at 1.
1. On the next page, in the "Media type" checkboxes, choose "Image".
1. Click on "Save settings".

If you are adding the field to more than one content type, you should choose "Re-use an existing field" on subsequent content types.

### Using the image in Views

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
