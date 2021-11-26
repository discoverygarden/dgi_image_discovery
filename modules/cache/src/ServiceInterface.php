<?php

namespace Drupal\dgi_image_discovery_cache;

use Drupal\media\MediaInterface;

use Drupal\Core\Cache\CacheableDepenencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;

interface ServiceInterface {

  public function get(ContentEntityInterface $entity) : ?MediaInterface;

  public function set(ContentEntityInterface $entity, MediaInterface $media, CacheableDependencyInterface $cache_meta) : void;

}
