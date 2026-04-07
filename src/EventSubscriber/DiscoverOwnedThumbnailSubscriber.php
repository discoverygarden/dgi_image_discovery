<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

/**
 * Discover thumbnails which are owned by a given object.
 */
class DiscoverOwnedThumbnailSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 900;

  /**
   * The media storage service of which to query/load media objects.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $mediaStorage;

  /**
   * Flag to allow falling back to original file if thumbnail is absent.
   *
   * @var bool
   */
  protected bool $fallbackToOriginalFile = FALSE;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
    $this->fallbackToOriginalFile = filter_var(
      getenv('DGI_IMAGE_DISCOVERY__FALLBACK_TO_ORIGINAL_FILE') ?: 'false',
      FILTER_VALIDATE_BOOLEAN,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $node = $event->getEntity();

    if (!($node instanceof NodeInterface)) {
      return;
    }

    $results = $this->mediaStorage->getQuery()
      ->condition('field_media_of', $node->id())
      ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#ThumbnailImage')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    // If there is no thumbnail, see if there is an Original File Image Media
    // entity to style as a thumbnail instead.
    if (empty($results) && $this->fallbackToOriginalFile) {
      $results = $this->mediaStorage->getQuery()
        ->condition('field_media_of', $node->id())
        ->condition('bundle', 'image')
        ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#OriginalFile')
        ->accessCheck()
        ->range(0, 1)
        ->execute();
    }

    $event->addCacheTags(['media_list']);

    if ($results) {
      $media = $this->mediaStorage->load(reset($results));

      $event->addCacheableDependency($media->access('view', NULL, TRUE))
        ->setMedia($media)
        ->stopPropagation();
    }
  }

}
