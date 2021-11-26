<?php

namespace Drupal\dgi_image_discovery\Plugin\Field\FieldType;

use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;

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
class DIDImageItem extends EntityReferenceItem implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  protected ImageDiscoveryInterface $discoveryService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    DataDefinitionInterface $definition,
    $name = NULL,
    TypedDataInterface $parent = NULL
  ) {
    parent::__construct($definition, $name, $parent);

    // XXX: DI not presently possible.
    // @see https://www.drupal.org/project/drupal/issues/2053415
    $this->discoveryService = \Drupal::service('dgi_image_discovery.service');
  }

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
        if ($value = $this->discoveryService->getImage($entity)) {
          $this->setValue($value);
        }
      }
      $this->isCalculated = TRUE;
    }
  }

}
