<?

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiscoverChildThumbnailSubscriber extends AbstractImageDiscoverySubscriver {

  const PRIORITY = 800;

  /**
   * @var int
   */
  protected int $depth;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $nodeStorage;

  /**
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   */
  protected ImageDiscoveryInterface $imageDiscovery;

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
  public function discoverImage(ImageDiscoveryImage $event) : void {
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
        ->accessCheck()
        ->range(0, 1)
        ->execute();

      $event->addCacheTags(['node_list'])

      if ($results) {
        $child = $this->nodeStorage->load(reset($results));

        $event->addCacheableDependency($child->access('view', NULL, TRUE));

        $result = $this->getImage($child);
        if ($result) {
          $event->setMedia($result)
            ->stopPropagation();
        }
      }
    }
    finally {
      $this->depth -= 1;
    }

  }

}
