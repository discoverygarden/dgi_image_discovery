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
collections, compounds and paged objects.

## Configuration

### Adding the field to your content type

You will need to add a new field to each of your applicable content types. To do this:

1. In the "Manage fields" page for your content type, choose "Create a new field".
1. In the "Add a new field" list, choose "Reference > Media".
1. Since the machine name of this field must be `field_representative_image`, you will need to give the new field the lable "Representative image" when you create it. You can can change this label later if you wish.
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
