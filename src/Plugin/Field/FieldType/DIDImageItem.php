<?php

namespace Drupal\dgi_image_discovery\Plugin\Field\FieldType;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;

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

  /**
   * The image discovery service.
   *
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   */
  protected ImageDiscoveryInterface $discoveryService;

  /**
   * Memoize that we have already determined the value.
   *
   * @var bool
   */
  protected bool $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    DataDefinitionInterface $definition,
    $name = NULL,
    ?TypedDataInterface $parent = NULL,
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
        $event = $this->discoveryService->getImage($entity);
        $this->addCacheableDependency($event);
        if ($event->hasMedia()) {
          $this->setValue($event->getMedia());
        }
      }
      $this->isCalculated = TRUE;
    }
  }

}
