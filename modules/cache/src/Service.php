<?php

namespace Drupal\dgi_image_discovery_cache;

use Drupal\Core\Cache\CacheBackendInterface;


class Service implements ServiceInterface {

  protected CacheBackendInterface $cache;

  public function __construct(
    CacheBackendInterface $cache,
    CacheContextsManager $cache_context_manager
  ) {
    $this->cache = $cache;
  }

  /**
   * Create a cache ID for the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to generate a cache ID.
   *
   * @return string
   *   A cache ID.
   */
  protected function createCacheID(ContentEntityInterface $entity, CacheableDependencyInterface $cache_meta) {
    $cid_parts = [
       $entity->getEntityTypeId(),
       $entity->id(),
    ];
    $contexts = $cache_meta->getCacheContexts();
    if (!empty($contexts)) {
      $context_cache_keys = $this->cacheContextsManager->convertTokensToKeys($contexts);
      $cid_parts = array_merge($cid_parts, $context_cache_keys->getKeys());
      $this->setCacheability(
        CacheableMetadata::createFromObject($this)
          ->merge($context_cache_keys)
      );
    }
    $cid = implode(':', $cid_parts);
    \ddm($cid);
    return $cid;
  }

  public function get(ContentEntityInterface $entity) : ?MediaInterface {
    $cache_meta = CacheableMetadata::createFromObject($entity)
      ->addCacheableDependency($entity->access('view', NULL, TRUE));
    if ($value = $this->cache->get(this->createCacheId($entity, $cache_meta))) {
      return $value->data;
    }

    return NULL;
  }

  public function set(ContentEntityInterface $entity, MediaInterface $media, CacheableDependencyInterface $cache_meta) : void {
    $this->cache->set(
      $this->createCacheID($entity, $cache_meta),
      $media,
      $cache_meta->getCacheTags()
    );
  }

}
