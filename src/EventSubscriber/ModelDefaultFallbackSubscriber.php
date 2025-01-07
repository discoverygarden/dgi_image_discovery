<?php

namespace Drupal\dgi_image_discovery\EventSubscriber;

use Drupal\dgi_image_discovery\ImageDiscoveryEvent;
use Drupal\node\NodeInterface;

/**
 * Model default fallback image subscriber.
 */
class ModelDefaultFallbackSubscriber extends AbstractImageDiscoverySubscriber {

  public const PRIORITY = 500;

  /**
   * {@inheritDoc}
   */
  public function discoverImage(ImageDiscoveryEvent $event) : void {
    $entity = $event->getEntity();
    if (!$entity instanceof NodeInterface) {
      return;
    }
    if (!$entity->hasField('field_model')) {
      return;
    }
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $term_item */
    foreach ($entity->get('field_model') as $term_item) {
      /** @var \Drupal\taxonomy\TermInterface $term */
      $term = $term_item?->get('entity')?->getTarget()?->getEntity();
      $term_access = $term?->access('view', NULL, TRUE);

      if (!$term_access?->isAllowed()) {
        continue;
      }

      if (!$term?->hasField('field_default_image')) {
        continue;
      }

      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $candidate_item */
      foreach ($term->get('field_default_image') as $candidate_item) {

        /** @var \Drupal\media\Entity\Media $candidate */
        if (!$candidate = $candidate_item?->get('entity')?->getTarget()?->getEntity()) {
          continue;
        }

        $candidate_access = $candidate?->access('view', NULL, TRUE);
        if (!$candidate_access->isAllowed()) {
          continue;
        }

        $event->addCacheableDependency($term_access)
          ->addCacheableDependency($candidate_access)
          ->addCacheableDependency($term)
          ->addCacheableDependency($candidate)
          ->setMedia($candidate)
          ->stopPropagation();
        return;
      }
    }
  }

}
