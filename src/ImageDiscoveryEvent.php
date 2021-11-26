<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;

use Symfony\Contracts\EventDispatcher\Event;

class ImageDiscoveryEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  const EVENT_NAME = 'dgi_image_discovery.event';

  protected ContentEntityInterface $entity;
  protected ?MediaInterface $media = NULL;

  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
    $this->addCacheableDependency($entity);
  }

  public function getEntity() : ContentEntityInterface {
    return $this->entity;
  }

  public function getMedia() : ?MediaInterface {
    return $this->media;
  }

  public function hasMedia() : bool {
    return $this->getMedia() !== NULL;
  }

  public function setMedia(MediaInterface $media) : ImageDiscoveryEvent {
    $this->media = $media;
    $this->addCacheableDependency($media);
    return $this;
  }

}
