<?php

namespace Drupal\dgi_image_discovery\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Http\Exception\CacheableHttpException;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
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
    protected RendererInterface $renderer,
  ) {}

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.dgi_image_discovery.url_generator.deferred'),
      $container->get('renderer'),
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
    $context = new RenderContext();
    /** @var \Drupal\Core\Cache\CacheableResponseInterface $response */
    $response = $this->renderer->executeInRenderContext($context, function () use ($style, $node) {
      // @todo Make plugin configurable?
      /** @var \Drupal\dgi_image_discovery\DeferredResolutionInterface $plugin */
      $plugin = $this->deferredResolutionPluginManager->createInstance(getenv('DGI_IMAGE_DISCOVERY_DEFERRED_PLUGIN') ?: 'subrequest');

      try {
        return $plugin->resolve($node, $style);
      }
      catch (CacheableHttpException $e) {
        return (new CacheableResponse($e->getMessage(), $e->getStatusCode()))
          ->addCacheableDependency($e);
      }
    });

    if (!$context->isEmpty()) {
      $metadata = $context->pop();
      $response->addCacheableDependency($metadata);
    }

    // Add some additional contexts representing this particular request.
    $cache_meta = (new CacheableMetadata())
      ->addCacheContexts(['route', 'url.path']);

    return $response->addCacheableDependency($cache_meta);

  }

}
