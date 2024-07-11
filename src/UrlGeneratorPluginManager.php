<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery;

use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\dgi_image_discovery\Attribute\UrlGenerator;

/**
 * UrlGenerator plugin manager.
 */
final class UrlGeneratorPluginManager extends DefaultPluginManager implements UrlGeneratorPluginManagerInterface, FallbackPluginManagerInterface {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/dgi_image_discovery/url_generator', $namespaces, $module_handler, UrlGeneratorInterface::class, UrlGenerator::class);
    $this->alterInfo('dgi_image_discovery__url_generator_info');
    $this->setCacheBackend($cache_backend, 'dgi_image_discovery__url_generator_plugins');
  }

  /**
   * {@inheritDoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) : string {
    return 'pre_generated';
  }

}
