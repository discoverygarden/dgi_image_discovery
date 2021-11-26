<?

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
   */
   abstract public function discoverImage(ImageDiscoveryImage $event) : void;

}
