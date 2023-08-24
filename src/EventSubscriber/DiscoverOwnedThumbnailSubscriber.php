<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->mediaStorage = $entity_type_manager->getStorage('media');
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
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    $event->addCacheTags(['media_list']);

    foreach ($results as $result) {
      $media = $this->mediaStorage->load($result);
      $media_access = $media->access('view', NULL, TRUE);
      $event->addCacheableDependency($media_access)
        ->addCacheableDependency($media);

      if (!$media_access->isAllowed()) {
        continue;
      }

      $event->setMedia($media)
        ->stopPropagation();
    }
  }

}
