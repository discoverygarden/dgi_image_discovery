<?php

namespace Drupal\dgi_image_discovery\Plugin\views\field;

use Drupal\views\Plugin\views\field\EntityField;

/**
 * A handler to provide proper displays for our discovered image.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("did_image")
 */
class DGIImageDiscoveryDIDImage extends EntityField {

  use ComputedDIDImageTrait;

}
