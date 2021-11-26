<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract base subscriber, to handle some common boiler-plating.
 */
abstract class AbstractImageDiscoverySubscriber implements EventSubscriberInterface {

  const PRIORITY = 0;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ImageDiscoveryEvent::EVENT_NAME => ['discoverImage', static::PRIORITY],
    ];
  }

  /**
   * Event callback; add in our handler.
   *
   * Expected to do something and ::setMedia() on the given event.
   */
  abstract public function discoverImage(ImageDiscoveryEvent $event) : void;

}
