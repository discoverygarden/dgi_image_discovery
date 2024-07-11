<?php

declare(strict_types=1);

namespace Drupal\dgi_image_discovery\Plugin\dgi_image_discovery\url_generator;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dgi_image_discovery\Attribute\UrlGenerator;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\dgi_image_discovery\UrlGeneratorPluginBase;
use Drupal\image\ImageStyleInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Plugin implementation for pre-generated URLs.
 */
#[UrlGenerator(
  id: "pre_generated",
  label: new TranslatableMarkup("Pre-generated URLs"),
  description: new TranslatableMarkup("Generate full URLs to styled images that should be used. Can be problematic with cache invalidation and/or indexing."),
)]
class PreGenerated extends UrlGeneratorPluginBase implements ContainerFactoryPluginInterface {

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
   * {@inheritDoc}
   */
  public function generate(NodeInterface $node, ImageStyleInterface $style): ?GeneratedUrl {
    try {
      return $this->getGeneratedUrl($node, $style);
    }
    catch (NotFoundHttpException) {
      return NULL;
    }
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->imageDiscovery = $container->get('dgi_image_discovery.service');
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  protected function getImageDiscovery(): ImageDiscoveryInterface {
    return $this->imageDiscovery;
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

}
