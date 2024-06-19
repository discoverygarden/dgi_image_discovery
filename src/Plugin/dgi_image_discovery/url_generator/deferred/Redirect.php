<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator\deferred;

use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dgi_image_discovery\Attribute\DeferredResolution;
use Drupal\dgi_image_discovery\DeferredResolutionPluginBase;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;

/**
 * Plugin implementation for deferred URL resolution via redirect.
 */
#[DeferredResolution(
  id: "redirect",
  label: new TranslatableMarkup("Redirect to styled image URL"),
  description: new TranslatableMarkup("Explicitly redirect to the styled image URL"),
)]
class Redirect extends DeferredResolutionPluginBase {

  /**
   * {@inheritDoc}
   */
  public function resolve(NodeInterface $node, ImageStyleInterface $style): CacheableResponseInterface {
    $generated_url = $this->getGeneratedUrl($node, $style);

    return (new CacheableRedirectResponse($generated_url->getGeneratedUrl()))
      ->addCacheableDependency($generated_url);
  }

}
