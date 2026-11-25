<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service provider.
 */
class DgiImageDiscoveryServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritDoc}
   */
  public function register(ContainerBuilder $container) {
    $container->setParameter(
      'dgi_image_discovery.deferred.plugin',
      (string) getenv('DGI_IMAGE_DISCOVERY_DEFERRED_PLUGIN') ?: 'subrequest',
    );
    $container->setParameter(
      'dgi_image_discovery.deferred.subrequest.auto_cache_etag',
        static::getBoolVar('DGI_IMAGE_DISCOVERY_DEFERRED_SUBREQUEST_AUTO_CACHE_ETAG', TRUE),
    );
    $container->setParameter(
      'dgi_image_discovery.event_subscriber.owned_thumbnail.fallback_to_original_file',
      static::getBoolVar('DGI_IMAGE_DISCOVERY__FALLBACK_TO_ORIGINAL_FILE', FALSE),
    );
  }

  /**
   * Helper; parse an environment variable containing a boolean value.
   *
   * @param string $name
   *   The name of the environment variable to parse.
   * @param bool $default
   *   The default value to use if the environment variable is not in the
   *   environment.
   *
   * @return bool
   *   The parsed value if provided; otherwise, the provided default.
   */
  private static function getBoolVar(string $name, bool $default) : bool {
    $value = getenv($name);

    if ($value === FALSE) {
      return $default;
    }

    $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($filtered === NULL) {
      throw new \InvalidArgumentException("Invalid value for $name.");
    }
    return $filtered;
  }

}
