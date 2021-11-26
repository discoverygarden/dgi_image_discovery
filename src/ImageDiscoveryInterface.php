<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Discovery service interface.
 */
interface ImageDiscoveryInterface {

  /**
   * Attempt get to get an image representing the given node.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to discover an image.
   *
   * @return \Drupal\dgi_image_discovery\ImageDiscoveryEvent
   *   The result of image discovery.
   */
  public function getImage(ContentEntityInterface $entity) : ImageDiscoveryEvent;

}
