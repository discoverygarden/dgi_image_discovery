<?php

namespace Drupal\dgi_image_discovery;

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

interface ImageDiscoveryInterface {

  /**
   * Attempt get to get an image representing the given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to discover an image.
   *
   * @return \Drupal\media\MediaInterface|null
   *   The discovered media if one could be found; otherwise, null.
   */
  public function getImage(NodeInterface $node) : ?MediaInterface;

}
