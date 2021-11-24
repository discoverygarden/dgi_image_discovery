<?php

namespace Drupal\dgi_image_discovery\Plugin\views\field;

use Drupal\node\NodeInterface;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\EntityField;

trait ComputedDIDImageTrait {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $relationship_entities = $values->_relationship_entities;
    $image = '';
    // First check the referenced entity.
    if (isset($relationship_entities['node'])) {
      $node = $relationship_entities['node'];
    }
    else {
      $node = $values->_entity;
    }

    if ($node instanceof NodeInterface) {
      $image = $node->get('did_image')->getValue();
    }

    return $image;
  }

  /**
   * {@inheritdoc}
   */
  public function query($use_groupby = FALSE) {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
