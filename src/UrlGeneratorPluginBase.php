<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for dgi_image_discovery__url_generator plugins.
 */
abstract class UrlGeneratorPluginBase extends PluginBase implements UrlGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
