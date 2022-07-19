<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

/**
 * Representative image discovery.
 */
class DiscoverRepresentativeImageSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 1000;

  /**
   * {@inheritdoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $entity = $event->getEntity();

    if (!($entity instanceof NodeInterface)) {
      return;
    }

    if ($representative = ($entity->field_representative_image->entity ?? FALSE)) {
      $access = $representative->access('view', NULL, TRUE);
      $event->addCacheableDependency($access);

      if ($access->isAllowed()) {
        $event->setMedia($representative)
          ->stopPropagation();
      }
    }
  }

}
