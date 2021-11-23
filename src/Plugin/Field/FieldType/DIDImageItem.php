<?php

namespace Drupal\dgi_image_discovery\Plugin\Field\FieldType;

use Drupal\node\NodeInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/*
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id", "width", "height"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "alt" = {
 *       "label" = @Translation("Alt"),
 *       "translatable" = TRUE
 *     },
 *     "title" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *   },
 */

/**
 * Variant of the 'image' field that does a lookup from the given node.
 *
 * @FieldType(
 *   id = "did_image",
 *   label = @Translation("DGI Image Discovery"),
 *   description = @Translation("An image relevant to the given node."),
 *   default_widget = "image_image",
 *   default_formatter = "image",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class DIDImageItem extends ImageItem {

  /**
   * Whether or not the value has been calculated.
   *
   * @var bool
   */
  protected $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }
  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  protected function ensureCalculated() {
    if (!$this->isCalculated) {
      $entity = $this->getEntity();
      if (!$entity->isNew()) {
        if ($value = $this->getImage($entity)) {
          $this->setValue($value);
        }
      }
      $this->isCalculated = TRUE;
    }
  }

  protected function getImage(NodeInterface $node, $depth = 0) {
    // Lookup representative image, return if found; otherwise,
    if ($value = $this->getRepresentative($node)) {
      return $value;
    }
    // Lookup thumbnail from the current object, return if found; otherwise,
    if ($value = $this->getOwnedThumbnail($node)) {
      return $value;
    }
    // Find first child object and call return the result of this method with
    // it; otherwise...
    if (($depth < 5) && ($value = $this->getFromFirstChild($node, $depth + 1))) {
      return $node;
    }

    // ... we set nothing, and expect whatever is using this to deal with
    // getting nothing however they like.
    return NULL;
  }

  protected function getRepresentative(NodeInterface $node) {
    $representative = $node->field_representative_image->field_media_image->getValue();
    return $representative ?? NULL;
  }

  protected function getOwnedThumbnail(NodeInterface $node) {
    // XXX: DI not presently possible.
    // @see https://www.drupal.org/project/drupal/issues/2053415
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $storage = $entity_type_manager->getStorage('media');
    $results = $storage->getQuery()
      ->condition('field_media_of', $node->id())
      ->condition('field_media_use.entity:field_external_uri.uri', 'http://pcdm.org/use#ThumbnailImage')
      ->range(0, 1)
      ->execute();

    if ($results) {
      return $storage->load(reset($results))->field_media_image->getValue();
    }
  }

  protected function getFromFirstChild(NodeInterface $node, $depth) {
    // XXX: DI not presently possible.
    // @see https://www.drupal.org/project/drupal/issues/2053415
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $storage = $entity_type_manager->getStorage('node');

    $results = $storage->getQuery()
      ->condition('field_member_of', $node->id())
      ->sort('field_weight')
      ->range(0, 1)
      ->execute();

    if ($results) {
      return $this->getImage($this->storage->load(reset($results)), $depth);
    }
  }

}
