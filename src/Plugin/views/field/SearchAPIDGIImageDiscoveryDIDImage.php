<?php

namespace Drupal\dgi_image_discovery\Plugin\views\field;

use Drupal\search_api\Plugin\views\field\SearchApiEntityField;

/**
 * A handler to provide proper displays for our discovered image.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("search_api_did_image")
 */
class SearchAPIDGIImageDiscoveryDIDImage extends SearchApiEntityField {

  use ComputedDIDImageTrait;

}
