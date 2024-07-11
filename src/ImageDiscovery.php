<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Entity\ContentEntityInterface;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Find image media related to a given node.
 */
class ImageDiscovery implements ImageDiscoveryInterface {

  /**
   * Constructor.
   */
  public function __construct(
    protected EventDispatcherInterface $eventDispatcher,
  ) {
    // No-op, other than setting property.
  }

  /**
   * {@inheritdoc}
   */
  public function getImage(ContentEntityInterface $entity) : ImageDiscoveryEvent {
    // Discover the media...
    $event = $this->eventDispatcher->dispatch(
      new ImageDiscoveryEvent($entity),
      ImageDiscoveryEvent::EVENT_NAME
    );

    // ... and allow things to respond/interact with what was discovered, before
    // it is used.
    $post_event = $this->eventDispatcher->dispatch(
      new ImageDiscoveredEvent($event),
      ImageDiscoveredEvent::EVENT_NAME
    );

    return $post_event->getEvent();
  }

}
