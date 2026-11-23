<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

/**
 * Discover thumbnails which are owned by a given object.
 */
class DiscoverOwnedThumbnailSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 900;

  /**
   * Constructor.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected bool $fallbackToOriginalFile,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $node = $event->getEntity();

    if (!($node instanceof NodeInterface)) {
      return;
    }

    $media_storage = $this->entityTypeManager->getStorage('media');

    $results = $media_storage->getQuery()
      ->condition('field_media_of', $node->id())
      ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#ThumbnailImage')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    // If there is no thumbnail, see if there is an Original File Image Media
    // entity to style as a thumbnail instead.
    if (empty($results) && $this->fallbackToOriginalFile) {
      $results = $media_storage->getQuery()
        ->condition('field_media_of', $node->id())
        ->condition('bundle', 'image')
        ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#OriginalFile')
        ->accessCheck()
        ->range(0, 1)
        ->execute();
    }

    $event->addCacheTags(['media_list']);

    if ($results) {
      $media = $media_storage->load(reset($results));

      $event->addCacheableDependency($media->access('view', NULL, TRUE))
        ->setMedia($media)
        ->stopPropagation();
    }
  }

}
