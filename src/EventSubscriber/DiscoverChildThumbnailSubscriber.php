<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\node\NodeInterface;

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
   * Constructor.
   */
  public function __construct(
    protected ImageDiscoveryInterface $imageDiscovery,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
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
    if ($this->depth + 1 > 3) {
      // Exhausted depth.
      return;
    }

    try {
      $this->depth += 1;

      $node_storage = $this->entityTypeManager->getStorage('node');

      $results = $node_storage->getQuery()
        ->condition('field_member_of', $node->id())
        ->sort('field_weight')
        // XXX: field_weight is nullable or not unique, so break ties by sorting
        // on the node ID.
        ->sort('nid')
        ->accessCheck()
        ->range(0, 1)
        ->execute();

      $event->addCacheTags(['node_list']);

      if ($results) {
        $child = $node_storage->load(reset($results));

        $event->addCacheableDependency($child->access('view', NULL, TRUE));

        $child_event = $this->imageDiscovery->getImage($child);
        $event->addCacheableDependency($child_event);
        if ($child_event->hasMedia()) {
          $event->setMedia($child_event->getMedia())
            ->stopPropagation();
        }
      }
    }
    finally {
      $this->depth -= 1;
    }

  }

}
