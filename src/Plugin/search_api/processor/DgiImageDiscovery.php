<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\dgi_image_discovery\Plugin\search_api\processor\Property\DgiImageDiscoveryProperty;
use Drupal\dgi_image_discovery\UrlGeneratorPluginManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get the styled image url for the islandora node.
 *
 * @SearchApiProcessor(
 *   id = "dgi_image_discovery",
 *   label = @Translation("DGI Image Discovery"),
 *   description = @Translation("Get the styled image url for the islandora node."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class DgiImageDiscovery extends ProcessorPluginBase implements ContainerFactoryPluginInterface {

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
    EntityTypeManagerInterface $entity_type_manager,
    protected UrlGeneratorPluginManagerInterface $urlGeneratorPluginManager,
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.dgi_image_discovery.url_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('DGI Image Discovery'),
        'description' => $this->t('Styled Image Url which can then be passed to the image src.'),
        'type' => 'string',
        'is_list' => FALSE,
        'processor_id' => $this->getPluginId(),
        'dgi_image_discovery__url_generator_options' => $this->getGeneratorOptions(),
      ];
      $properties['dgi_image_discovery'] = new DgiImageDiscoveryProperty($definition);
    }

    return $properties;
  }

  /**
   * Helper; get listing of generators for use as form options.
   *
   * @return string[]
   *   An array mapping plugin IDs to human-readable strings.
   */
  protected function getGeneratorOptions() : array {
    $options = [];

    foreach ($this->urlGeneratorPluginManager->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $entity = $item->getOriginalObject()->getValue();

    // Get the image discovery URL.
    if (!$entity->isNew() && $entity instanceof NodeInterface) {
      $fields = $item->getFields(FALSE);
      $fields = $this->getFieldsHelper()->filterForPropertyPath($fields, NULL, 'dgi_image_discovery');
      foreach ($fields as $field) {
        $config = $field->getConfiguration();
        /** @var \Drupal\image\ImageStyleInterface $image_style */
        $image_style = $this->entityTypeManager->getStorage('image_style')->load($config['image_style']);
        /** @var \Drupal\dgi_image_discovery\UrlGeneratorInterface $url_generator */
        $url_generator = $this->urlGeneratorPluginManager->createInstance($config['url_generator'] ?? 'pre_generated');
        $generated_url = $url_generator->generate($entity, $image_style);
        if ($generated_url) {
          $field->addValue($generated_url->getGeneratedUrl());
        }
        else {
          // Fallback to default image if URL generation fails.
          $default_image_url = $this->getDefaultImageFromTaxonomy($entity, $image_style_name);
          if ($default_image_url) {
            $field->addValue($default_image_url);
          }
        }
      }
    }
  }

  /**
   * Gets the default image URL from the taxonomy term.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to get the default image from.
   * @param string $image_style_name
   *   The image style to use.
   *
   * @return string|null
   *   The default image URL or null if not found.
   */
  protected function getDefaultImageFromTaxonomy(NodeInterface $node, string $image_style_name) {
    $default_image_url = NULL;
    $model_terms = $node->get('field_model')->referencedEntities();

    foreach ($model_terms as $term) {
      if ($term instanceof \Drupal\taxonomy\Entity\Term) {
        // Load the media entity referenced by the field_defaultimage.
        $media = $term->get('field_defaultimage')->entity;
        if ($media instanceof \Drupal\media\Entity\Media) {
          // Load the file entity from the media entity.
          $file = $media->get('field_media_image')->entity;
          if ($file instanceof \Drupal\file\Entity\File) {
            // Use the provided image style.
            $default_image_url = $this->entityTypeManager->getStorage('image_style')->load($image_style_name)
              ->buildUrl($file->getFileUri());
            // Return the first default image found, if applicable.
            break;
          }
        }
      }
    }

    return $default_image_url;
  }

}
