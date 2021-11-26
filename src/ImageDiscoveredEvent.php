<?php

namespace Drupal\dgi_image_discovery;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Wrap an event for knock-on reaction.
 */
class ImageDiscoveredEvent extends Event {

  const EVENT_NAME = 'dgi_image_discovery.post_event';

  /**
   * The image discovery event to which we are responding.
   *
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryEvent
   */
  protected ImageDiscoveryEvent $event;

  /**
   * Constructor.
   */
  public function __construct(ImageDiscoveryEvent $event) {
    $this->event = $event;
  }

  /**
   * Get the wrapped event.
   *
   * @return \Drupal\dgi_image_discovery\ImageDiscoveryEvent
   *   The event.
   */
  public function getEvent() : ImageDiscoveryEvent {
    return $this->event;
  }

}
