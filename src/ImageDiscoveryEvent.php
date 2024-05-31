<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\media\MediaInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Image discovery event info.
 */
class ImageDiscoveryEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  const EVENT_NAME = 'dgi_image_discovery.event';

  /**
   * The entity for which to discovery a representative media.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected ContentEntityInterface $entity;

  /**
   * The discovered media.
   *
   * @var \Drupal\media\MediaInterface|null
   */
  protected ?MediaInterface $media = NULL;

  /**
   * Constructor.
   */
  public function __construct(ContentEntityInterface $entity) {
    $this->entity = $entity;
    $this->addCacheableDependency($entity);
  }

  /**
   * Entity accessor.
   */
  public function getEntity() : ContentEntityInterface {
    return $this->entity;
  }

  /**
   * Entity accessor.
   */
  public function getMedia() : ?MediaInterface {
    return $this->media;
  }

  /**
   * Entity accessor.
   */
  public function hasMedia() : bool {
    return $this->getMedia() !== NULL;
  }

  /**
   * Entity setter.
   */
  public function setMedia(MediaInterface $media) : ImageDiscoveryEvent {
    $this->media = $media;
    $this->addCacheableDependency($media);
    return $this;
  }

}
