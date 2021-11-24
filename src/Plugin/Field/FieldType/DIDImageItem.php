<?php

namespace Drupal\dgi_image_discovery\Plugin\Field\FieldType;

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Find image media related to the given node.
 *
 * @FieldType(
 *   id = "did_image",
 *   label = @Translation("DGI Image Discovery"),
 *   description = @Translation("An image relevant to the given node."),
 *   default_formatter = "media_thumbnail",
 *   constraints = {"ReferenceAccess" = {}},
 *   list_class = "\Drupal\dgi_image_discovery\DIDImageItemList",
 * )
 */
class DIDImageItem extends EntityReferenceItem {

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

  protected function getImage(NodeInterface $node, $depth = 0) : ?MediaInterface {
    // Lookup representative image, return if found; otherwise,
    if ($value = $this->getRepresentative($node)) {
      ddm("got representative for {$node->id()}");
      return $value;
    }
    // Lookup thumbnail from the current object, return if found; otherwise,
    if ($value = $this->getOwnedThumbnail($node)) {
      ddm("got owned for {$node->id()}");
      return $value;
    }
    // Find first child object and call return the result of this method with
    // it; otherwise...
    if (($depth < 5) && ($value = $this->getFromFirstChild($node, $depth + 1))) {
      ddm($depth, "got child for {$node->id()}");
      return $value;
    }

    ddm("found nothing for {$node->id()}");
    // ... we set nothing, and expect whatever is using this to deal with
    // getting nothing however they like.
    return NULL;
  }

  protected function getRepresentative(NodeInterface $node) : ?MediaInterface {
    $representative = $node->field_representative_image->entity;
    if ($representative && $representative->access('view')) {
      return $representative;
    }

    return NULL;
  }

  protected function getOwnedThumbnail(NodeInterface $node) : ?MediaInterface {
    // XXX: DI not presently possible.
    // @see https://www.drupal.org/project/drupal/issues/2053415
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $storage = $entity_type_manager->getStorage('media');
    $results = $storage->getQuery()
      ->condition('field_media_of', $node->id())
      ->condition('field_media_use.entity:taxonomy_term.field_external_uri.uri', 'http://pcdm.org/use#ThumbnailImage')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    if ($results) {
      return $storage->load(reset($results));
    }

    return NULL;
  }

  protected function getFromFirstChild(NodeInterface $node, $depth) : ?MediaInterface {
    // XXX: DI not presently possible.
    // @see https://www.drupal.org/project/drupal/issues/2053415
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $storage = $entity_type_manager->getStorage('node');

    $results = $storage->getQuery()
      ->condition('field_member_of', $node->id())
      ->sort('field_weight')
      ->accessCheck()
      ->range(0, 1)
      ->execute();

    if ($results) {
      return $this->getImage($storage->load(reset($results)), $depth);
    }

    return NULL;
  }

}
