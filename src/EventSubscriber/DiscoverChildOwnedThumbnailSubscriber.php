<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Discover thumbnails which are owned by children of a given object.
 */
class DiscoverChildOwnedThumbnailSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 850;

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
      ->condition('field_media_of.entity:node.field_member_of', $node->id())
      ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#ThumbnailImage')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    $event->addCacheTags(['media_list']);

    if ($results) {
      $media = $this->mediaStorage->load(reset($results));

      $event->addCacheableDependency($media->access('view', NULL, TRUE))
        ->setMedia($media)
        ->stopPropagation();
    }
  }

}
