<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Entity\ContentEntityInterface;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Find image media related to a given node.
 */
class ImageDiscovery implements ImageDiscoveryInterface {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->eventDispatcher = $event_dispatcher;
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
