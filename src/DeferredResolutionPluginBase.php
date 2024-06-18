<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator\UrlGenerationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for deferred_resolution plugins.
 */
abstract class DeferredResolutionPluginBase extends PluginBase implements DeferredResolutionInterface, ContainerFactoryPluginInterface {

  use UrlGenerationTrait;

  /**
   * Our image discovery service.
   *
   * @var \Drupal\dgi_image_discovery\ImageDiscoveryInterface
   */
  protected ImageDiscoveryInterface $imageDiscovery;

  /**
   * Drupal's entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getImageDiscovery(): ImageDiscoveryInterface {
    return $this->imageDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) : static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->imageDiscovery = $container->get('dgi_image_discovery.service');

    return $instance;
  }

}
