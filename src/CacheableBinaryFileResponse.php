<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheableResponseTrait;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Cacheable binary file response.
 *
 * Loosely adapted from
 * https://www.drupal.org/project/drupal/issues/3227041#comment-15335922
 */
class CacheableBinaryFileResponse extends BinaryFileResponse implements CacheableResponseInterface {

  use CacheableResponseTrait;
  use DependencySerializationTrait {
    __sleep as traitSleep;
    __wakeup as traitWakeup;
  }

  /**
   * Serializable reference to the file.
   *
   * @var string
   */
  protected string $uri;

  /**
   * {@inheritDoc}
   */
  public function setFile(\SplFileInfo|string $file, ?string $contentDisposition = NULL, bool $autoEtag = FALSE, bool $autoLastModified = TRUE): static {
    $this->uri = $file instanceof \SplFileInfo ? $file->getPathname() : $file;
    return parent::setFile($file, $contentDisposition, $autoEtag, $autoLastModified);
  }

  /**
   * {@inheritDoc}
   */
  public function __sleep() {
    return array_diff($this->traitSleep(), [
      'file',
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function __wakeup() : void {
    $this->traitWakeup();
    $this->setFile($this->uri);
  }

  /**
   * Convert a BinaryFileResponse into a CacheableBinaryFileResponse.
   *
   * @param \Symfony\Component\HttpFoundation\BinaryFileResponse $response
   *   The response to convert.
   *
   * @return static
   *   The converted response.
   */
  public static function convert(BinaryFileResponse $response) : static {
    return new static(
      $response->getFile(),
      $response->getStatusCode(),
      $response->headers->all(),
      /* $public, $contentDisposition, $autoEtag, $autoLastModified all accounted for in headers */
    );
  }

}
