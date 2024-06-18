<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery;

use Drupal\Core\GeneratedUrl;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;

/**
 * Interface for dgi_image_discovery__url_generator plugins.
 */
interface UrlGeneratorInterface {

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
   * @return \Drupal\Core\GeneratedUrl|null
   *   Generated URL; otherwise, NULL if none could be generated.
   */
  public function generate(NodeInterface $node, ImageStyleInterface $style): ?GeneratedUrl;

}
