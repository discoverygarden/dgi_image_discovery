<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\dgi_image_discovery\Utility;
use Drupal\islandora\IslandoraUtils;
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
      ->condition(IslandoraUtils::MEDIA_OF_FIELD, $node->id())
      ->condition(Utility::MEDIA_USE_URI_FIELD, Utility::THUMBNAIL_URI)
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    // If there is no thumbnail, see if there is an Original File Image Media
    // entity to style as a thumbnail instead.
    if (empty($results) && $this->fallbackToOriginalFile) {
      $results = $media_storage->getQuery()
        ->condition(IslandoraUtils::MEDIA_OF_FIELD, $node->id())
        ->condition('bundle', 'image')
        ->condition(Utility::MEDIA_USE_URI_FIELD, Utility::ORIGINAL_FILE_URI)
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
