<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get the styled image url for the islandora node.
 *
 * @SearchApiProcessor(
 *   id = "islandora_object_image_discovery",
 *   label = @Translation("Islandora Object Image Discovery"),
 *   description = @Translation("Get the styled image url for the islandora node."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class IslandoraObjectImageDiscovery extends ProcessorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The DGI Image Discovery service.
   *
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   */
  protected $imageDiscovery;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ImageDiscoveryInterface $image_discovery,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->imageDiscovery = $image_discovery;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dgi_image_discovery.service'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Islandora Object Image Discovery'),
        'description' => $this->t('Styled Image Url which can then be passed to the image src.'),
        'type' => 'string',
        'is_list' => FALSE,
        'processor_id' => $this->getPluginId(),
      ];
      $properties['islandora_object_image_discovery'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();
    $value = NULL;

    // Get the image discovery URL.
    if (!$entity->isNew() && $entity instanceof NodeInterface) {
      $event = $this->imageDiscovery->getImage($entity);
      if ($event->hasMedia()) {
        $image = $event->getMedia()->field_media_image;
        if (!empty($image)) {
          $value = $this->entityTypeManager->getStorage('image_style')->load('solr_grid_thumbnail')
            ->buildUrl($image->entity->getFileUri());
        }
      }
    }

    $fields = $item->getFields(FALSE);
    $fields = $this->getFieldsHelper()->filterForPropertyPath($fields, NULL, 'islandora_object_image_discovery');
    foreach ($fields as $field) {
      $field->addValue($value);
    }
  }

}
