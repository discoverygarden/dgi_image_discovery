<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator;

use Drupal\Core\GeneratedUrl;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\dgi_image_discovery\Attribute\UrlGenerator;
use Drupal\dgi_image_discovery\UrlGeneratorPluginBase;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;

/**
 * Plugin implementation for deferred URL resolution.
 */
#[UrlGenerator(
  id: "deferred",
  label: new TranslatableMarkup("Deferred URL resolution"),
  description: new TranslatableMarkup("Generate URLs making use of our deferred resolution endpoint."),
)]
class Deferred extends UrlGeneratorPluginBase {

  /**
   * {@inheritDoc}
   */
  public function generate(NodeInterface $node, ImageStyleInterface $style): GeneratedUrl {
    return Url::fromRoute(
      'dgi_image_discovery.deferred_resolution',
      [
        'node' => $node->id(),
        'style' => $style->id(),
      ],
      [
        'absolute' => TRUE,
        // XXX: Prevent use of aliased paths.
        'alias' => TRUE,
      ],
    )
      ->toString(TRUE)
      ->addCacheableDependency($node)
      ->addCacheableDependency($style);
  }

}
