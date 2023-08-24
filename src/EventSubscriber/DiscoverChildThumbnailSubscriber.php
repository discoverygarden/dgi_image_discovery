<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\node\NodeInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Discovery child thumbnails.
 */
class DiscoverChildThumbnailSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 800;

  /**
   * The "depth" to which we have traversed.
   *
   * To avoid infinite recursion, we track how far we have gone, so we can break
   * the chain somewhere.
   *
   * @var int
   */
  protected int $depth;

  /**
   * The node storage service of which to query/load nodes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $nodeStorage;

  /**
   * The image discovery service.
   *
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   */
  protected ImageDiscoveryInterface $imageDiscovery;

  /**
   * Constructor.
   */
  public function __construct(
    ImageDiscoveryInterface $image_discovery,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->imageDiscovery = $image_discovery;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->depth = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $node = $event->getEntity();

    if (!($node instanceof NodeInterface)) {
      return;
    }
    elseif ($this->depth + 1 > 3) {
      // Exhausted depth.
      return;
    }

    try {
      $this->depth += 1;

      $results = $this->nodeStorage->getQuery()
        ->condition('field_member_of', $node->id())
        ->sort('field_weight')
        // XXX: field_weight is nullable or not unique, so break ties by sorting
        // on the node ID.
        ->sort('nid')
        ->accessCheck(FALSE)
        ->range(0, 1)
        ->execute();

      $event->addCacheTags(['node_list']);

      foreach ($results as $result) {
        $child = $this->nodeStorage->load($result);

        $access_result = $child->access('view', NULL, TRUE);
        $event->addCacheableDependency($access_result)
          ->addCacheableDependency($child);

        if (!$access_result->isAllowed()) {
          continue;
        }

        $child_event = $this->imageDiscovery->getImage($child);
        $event->addCacheableDependency($child_event);
        if ($child_event->hasMedia()) {
          $media = $child_event->getMedia();
          $media_access = $media->access('view', NULL, TRUE);
          $event->addCacheableDependency($media_access)
            ->addCacheableDependency($media);
          if (!$media_access->isAllowed()) {
            continue;
          }
          $event->setMedia($media)
            ->stopPropagation();
          break;
        }
      }
    }
    finally {
      $this->depth -= 1;
    }

  }

}
