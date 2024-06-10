<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\UserSession;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\dgi_image_discovery\Plugin\search_api\processor\Property\DgiImageDiscoveryProperty;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\user\RoleInterface;
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
   * Drupal's account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected AccountSwitcherInterface $accountSwitcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ImageDiscoveryInterface $image_discovery,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->imageDiscovery = $image_discovery;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dgi_image_discovery.service'),
      $container->get('entity_type.manager')
    );

    $instance->accountSwitcher = $container->get('account_switcher');

    return $instance;
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
      ];
      $properties['dgi_image_discovery'] = new DgiImageDiscoveryProperty($definition);
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
    if (!(!$entity->isNew() && $entity instanceof NodeInterface)) {
      return;
    }

    $fields = $item->getFields(FALSE);
    $fields = $this->getFieldsHelper()->filterForPropertyPath($fields, NULL, 'dgi_image_discovery');
    foreach ($fields as $field) {
      try {
        $configuration = $field->getConfiguration();

        // XXX: Adapted from https://git.drupalcode.org/project/search_api/-/blob/8.x-1.x/src/Plugin/search_api/processor/RenderedItem.php#L184-190
        // If a (non-anonymous) role is selected, then also add the authenticated
        // user role.
        $roles = $configuration['roles'] ?? [RoleInterface::ANONYMOUS_ID];
        $authenticated = RoleInterface::AUTHENTICATED_ID;
        if (array_diff($roles, [$authenticated, RoleInterface::ANONYMOUS_ID])) {
          $roles[$authenticated] = $authenticated;
        }

        $this->accountSwitcher->switchTo(new UserSession(['roles' => array_values($roles)]));

        $event = $this->imageDiscovery->getImage($entity);
        $media = $event->getMedia();
        if (empty($media)) {
          continue;
        }

        $media_source = $media->getSource();
        $file_id = $media_source->getSourceFieldValue($media);
        $image = $this->entityTypeManager->getStorage('file')->load($file_id);
        if (empty($image)) {
          continue;
        }

        $image_style = $configuration['image_style'];
        $value = $this->entityTypeManager->getStorage('image_style')
          ->load($image_style)
          ->buildUrl($image->getFileUri());
        $field->addValue($value);
      }
      finally {
        $this->accountSwitcher->switchBack();
      }
    }
  }

}
