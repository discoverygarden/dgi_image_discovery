<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;

/**
 * Interface for deferred_resolution plugins.
 */
interface DeferredResolutionInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Generate URL for the given node/style.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to generate a URL.
   * @param \Drupal\image\ImageStyleInterface $style
   *   The style which the URL should return.
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   *   Cacheable resolution response.
   */
  public function resolve(NodeInterface $node, ImageStyleInterface $style): CacheableResponseInterface;

}
