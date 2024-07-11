<?php

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Http\Exception\CacheableNotFoundHttpException;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;

/**
 * Styled image URL generation trait.
 */
trait UrlGenerationTrait {

  /**
   * Get our image discovery service.
   *
   * @return \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   *   Our image discovery service.
   */
  abstract protected function getImageDiscovery() : ImageDiscoveryInterface;

  /**
   * Get Drupal's entity type manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   Drupal's entity type manager service.
   */
  abstract protected function getEntityTypeManager() : EntityTypeManagerInterface;

  /**
   * Generate URL for the image of the given node in the given style.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node of which to return an image.
   * @param \Drupal\image\ImageStyleInterface $style
   *   The style of the image to be returned.
   *
   * @return \Drupal\Core\GeneratedUrl
   *   The generated URL.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Http\Exception\CacheableNotFoundHttpException
   */
  public function getGeneratedUrl(NodeInterface $node, ImageStyleInterface $style) : GeneratedUrl {
    $generated_url = (new GeneratedUrl())
      ->addCacheableDependency($node)
      ->addCacheableDependency($style);

    $event = $this->getImageDiscovery()->getImage($node);
    $generated_url->addCacheableDependency($event);
    $media = $event->getMedia();
    if (empty($media)) {
      throw new CacheableNotFoundHttpException($generated_url, "No media discovered for node ({$node->id()}).");
    }

    $generated_url->addCacheableDependency($media);

    $media_source = $media->getSource();
    $file_id = $media_source->getSourceFieldValue($media);
    /** @var \Drupal\file\FileInterface|null $image */
    $image = $this->getEntityTypeManager()->getStorage('file')->load($file_id);
    if (empty($image)) {
      throw new CacheableNotFoundHttpException($generated_url, "File ID ({$file_id}) discovered for node ({$node->id()}) could not be loaded.");
    }

    $generated_url->addCacheableDependency($image);

    return $generated_url->setGeneratedUrl($style->buildUrl($image->getFileUri()));
  }

}
