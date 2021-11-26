<?php

namespace Drupal\dgi_image_discovery;

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Find image media related to a given node.
 */
class ImageDiscovery implements ImageDiscoveryInterface {

  /**
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
  public function getImage(NodeInterface $node) : ?MediaInterface {
    // Discover the media...
    $event = $this->eventDispatcher->dispatch(new ImageDiscoveryEvent($node), ImageDiscoveryEvent::EVENT_NAME);

    // ... and allow things to respond/interact with what was discovered.
    $post_event = $this->eventDispatcher->dispatch(new ImageDiscoveredEvent($event), ImageDiscoveredEvent::EVENT_NAME);

    return $post_event->getEvent()->getMedia();
  }

}
