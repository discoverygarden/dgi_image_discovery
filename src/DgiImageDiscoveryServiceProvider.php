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
      (string) filter_input(INPUT_ENV, 'DGI_IMAGE_DISCOVERY_DEFERRED_PLUGIN') ?: 'subrequest',
    );
    $container->setParameter(
      'dgi_image_discovery.deferred.subrequest.auto_cache_headers',
      (bool) (filter_input(INPUT_ENV, 'DGI_IMAGE_DISCOVERY_DEFERRED_SUBREQUEST_AUTO_CACHE_HEADERS', FILTER_VALIDATE_BOOLEAN) ?: TRUE),
    );
    $container->setParameter(
      'dgi_image_discovery.event_subscriber.owned_thumbnail.fallback_to_original_file',
      (bool) filter_input(INPUT_ENV, 'DGI_IMAGE_DISCOVERY__FALLBACK_TO_ORIGINAL_FILE', FILTER_VALIDATE_BOOLEAN)
    );
  }

}
