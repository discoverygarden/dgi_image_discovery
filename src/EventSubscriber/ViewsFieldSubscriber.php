<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\search_api\Event\MappingViewsFieldHandlersEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add in our field mapping.
 */
class ViewsFieldSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SearchApiEvents::MAPPING_VIEWS_FIELD_HANDLERS => ['fieldHandlers', -100],
    ];
  }

  /**
   * Event callback; add in our handler.
   */
  public function fieldHandlers(MappingViewsFieldHandlersEvent $event) {
    $mapping =& $event->getFieldHandlerMapping();

    $mapping['field_item:did_image'] = [
      'id' => 'search_api_did_image',
      'field_name' => 'did_image',
    ];

  }

}
