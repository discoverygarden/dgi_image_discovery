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
      'dgi_image_discovery.deferred.subrequest.auto_cache_headers',
      (bool)(filter_input(INPUT_ENV, 'DGI_IMAGE_DISCOVERY_DEFERRED_SUBREQUEST_AUTO_CACHE_HEADERS', FILTER_VALIDATE_BOOLEAN) ?: TRUE),
    );
  }

}
