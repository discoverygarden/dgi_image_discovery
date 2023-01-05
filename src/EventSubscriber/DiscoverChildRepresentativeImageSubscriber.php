<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

/**
 * Representative image discovery.
 */
class DiscoverChildRepresentativeImageSubscriber extends AbstractImageDiscoverySubscriber {

  const PRIORITY = 875;

  /**
   * The node storage service of which to query/load nodes.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $node = $event->getEntity();

    if (!($node instanceof NodeInterface)) {
      return;
    }

    $results = $this->nodeStorage->getQuery()
      ->condition('field_member_of', $node->id())
      ->exists('field_representative_image')
      ->sort('field_weight')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    $event->addCacheTags(['node_list']);

    if ($results) {
      $child = $this->nodeStorage->load(reset($results));
      if ($representative = ($child->field_representative_image->entity ?? FALSE)) {
        $access = $representative->access('view', NULL, TRUE);
        $event->addCacheableDependency($access);

        if ($access->isAllowed()) {
          $event->setMedia($representative)
            ->stopPropagation();
        }
      }
    }
  }

}
