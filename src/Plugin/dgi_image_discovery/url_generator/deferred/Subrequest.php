<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator\deferred;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\GeneratedUrl;
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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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
   * Current stack of requests being served.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Flag; if we should generate cache headers within PHP, automatically.
   *
   * @var bool
   */
  protected bool $autoCacheHeaders;

  /**
   * {@inheritDoc}
   */
  public function resolve(NodeInterface $node, ImageStyleInterface $style): CacheableResponseInterface {
    $generated_url = $this->getGeneratedUrl($node, $style);
    $response = NULL;

    $attempts = 3;
    while ($attempts-- > 0) {
      $current_request = $this->requestStack->getCurrentRequest();
      if (!$current_request) {
        throw new \LogicException('Unknown request.');
      }
      $request = Request::create(
        $generated_url->getGeneratedUrl(),
        cookies: $current_request->cookies->all(),
        server: $current_request->server->all(),
      );
      $request->setSession($current_request->getSession());
      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      if ($response instanceof BinaryFileResponse) {
        $to_return = CacheableBinaryFileResponse::convert($response)
          ->addCacheableDependency($generated_url);
        if ($this->autoCacheHeaders) {
          $to_return->setAutoEtag()
            ->setAutoLastModified();
        }
        return $to_return;
      }

      if ($response instanceof CacheableResponseInterface && ((int) ($response->getStatusCode() / 100)) === 3) {
        return $response;
      }

      if ($response->getStatusCode() === 503) {
        $after = $response->headers->get('Retry-After');
        if ($after === NULL) {
          // No value.
          throw $this->getExceptionFromResponse($response, $generated_url);
        }
        $after = (int) $after;
        if ($after > 0 && $after <= 5) {
          // If we have a requested time for which we are willing to wait, let's
          // wait it out. Image derivation indicates 3 seconds, but let's allow
          // up to 5 in the case of some other process requesting such, just to
          // be nice.
          sleep($after);
          continue;
        }
      }

      throw $this->getExceptionFromResponse($response, $generated_url);
    }

    throw $this->getExceptionFromResponse($response, $generated_url);
  }

  /**
   * Helper; build out cachable exception from the given response for the URL.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The response for which to build an exception.
   * @param \Drupal\Core\GeneratedUrl $generated_url
   *   The URL related to the response.
   *
   * @return \Drupal\Core\Http\Exception\CacheableHttpException
   *   The built exception.
   */
  protected function getExceptionFromResponse(Response $response, GeneratedUrl $generated_url) : CacheableHttpException {
    if ($after = $response->headers->get('Retry-After')) {
      $generated_url->mergeCacheMaxAge($after);
    }
    return new CacheableHttpException(
      $generated_url,
      $response->getStatusCode() ?? 500,
      $response->getContent() ?? '',
    );
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return parent::create($container, $configuration, $plugin_id, $plugin_definition)
      ->setHttpKernel($container->get('http_kernel'))
      ->setRequestStack($container->get('request_stack'))
      ->setAutoHeaders($container->getParameter('dgi_image_discovery.deferred.subrequest.auto_cache_headers'));
  }

  /**
   * Setter; set the HTTP kernel.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The HTTP kernel to set.
   *
   * @return $this
   *   Fluent API.
   */
  public function setHttpKernel(HttpKernelInterface $httpKernel) : static {
    $this->httpKernel = $httpKernel;
    return $this;
  }

  /**
   * Setter; set the request stack.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack to set.
   *
   * @return $this
   *   Fluent API.
   */
  public function setRequestStack(RequestStack $requestStack) : static {
    $this->requestStack = $requestStack;
    return $this;
  }

  /**
   * Setter; set flag to control auto header generation.
   *
   * @param bool $flag
   *   TRUE to generate etag/last-modified in PHP; otherwise, FALSE.
   *
   * @return $this
   *   Fluent API.
   */
  public function setAutoHeaders(bool $flag) : static {
    $this->autoCacheHeaders = $flag;
    return $this;
  }

}
