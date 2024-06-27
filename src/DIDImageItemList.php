<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Boiler-plate for our computed field.
 */
class DIDImageItemList extends EntityReferenceFieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $this->ensurePopulated();
  }

  /**
   * Computes the calculated values for this item list.
   */
  protected function ensurePopulated() {
    if (!isset($this->list[0])) {
      $item = $this->createItem(0);
      if (!$item->isEmpty()) {
        $this->list[0] = $item;
      }
    }
  }

}
