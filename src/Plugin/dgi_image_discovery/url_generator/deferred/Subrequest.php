<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Http\Exception\CacheableHttpException;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dgi_image_discovery\Attribute\DeferredResolution;
use Drupal\dgi_image_discovery\CacheableBinaryFileResponse;
use Drupal\dgi_image_discovery\DeferredResolutionPluginBase;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Plugin implementation for deferred URL resolution via subrequest.
 */
#[DeferredResolution(
  id: "subrequest",
  label: new TranslatableMarkup("Subrequest to stream the image directly."),
  description: new TranslatableMarkup("Perform a subrequest to stream the image directly."),
)]
class Subrequest extends DeferredResolutionPluginBase {

  /**
   * Drupal's HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected HttpKernelInterface $httpKernel;

  /**
   * {@inheritDoc}
   */
  public function resolve(NodeInterface $node, ImageStyleInterface $style): CacheableResponseInterface {
    $generated_url = $this->getGeneratedUrl($node, $style);

    $current_request = $this->httpKernel->getRequest();
    $request = Request::create($generated_url->getGeneratedUrl());
    $request->setSession($current_request->getSession());
    $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    if ($response instanceof BinaryFileResponse) {
      return (CacheableBinaryFileResponse::convert($response))
        ->addCacheableDependency($generated_url);
    }

    throw new CacheableHttpException($generated_url, $response->getStatusCode(), $response->getContent());
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $instance->httpKernel = $container->get('http_kernel');

    return $instance;
  }

}
