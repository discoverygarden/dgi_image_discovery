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
      dsm($term_item);
      $term = $term_item?->get('entity')?->getTarget()?->getEntity();
      dsm($term);
      $term_access = $term?->access('view', NULL, TRUE);
      dsm($term_access->isAllowed());
      if (!$term_access?->isAllowed()) {
        dsm("a");
        continue;
      }
      if (!$term?->hasField('field_default_image')) {
        dsm("b");
        continue;
      }
      dsm("c");
      $dt = $term->get('field_default_image');
      dsm($dt);
      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $candidate_item */
      foreach ($term->get('field_default_image') as $candidate_item) {
        dsm("d");
        dsm($candidate_item);
        $candidate = $candidate_item?->get('entity')?->getTarget()->getEntity();
        dsm($candidate);
//        /** @var \Drupal\media\MediaInterface $candidate */Drupal\file\Entity\File
//        /** @var \Drupal\file\Entity\File $candidate */
//        if ($candidate = $candidate_item?->get('entity')?->getTarget()->getEntity()) {
//          continue;
//        }
        dsm("e");
        $candidate_access = $candidate?->access('view', NULL, TRUE);
//        if (!$candidate_access->isAllowed()) {
//          continue;
//        }
        dsm("f");
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
