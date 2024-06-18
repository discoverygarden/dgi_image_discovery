<?php

namespace Drupal\dgi_image_discovery\Controller;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dgi_image_discovery\DeferredResolutionPluginManagerInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deferred image URL resolution controller.
 */
class DeferredResolutionController implements ContainerInjectionInterface {

  /**
   * Constructor.
   */
  public function __construct(
    protected DeferredResolutionPluginManagerInterface $deferredResolutionPluginManager,
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.dgi_image_discovery.url_generator.deferred'),
    );
  }

  /**
   * Resolve image for the given node and style.
   *
   * @param \Drupal\image\ImageStyleInterface $style
   *   The style of image to get.
   * @param \Drupal\node\NodeInterface $node
   *   The node of which to get an image.
   *
   * @return \Drupal\Core\Cache\CacheableResponseInterface
   *   A cacheable response.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function resolve(ImageStyleInterface $style, NodeInterface $node) : CacheableResponseInterface {
    // @todo Make plugin configurable.
    /** @var \Drupal\dgi_image_discovery\DeferredResolutionInterface $plugin */
    $plugin = $this->deferredResolutionPluginManager->createInstance('redirect');

    return $plugin->resolve($node, $style);
  }

}
