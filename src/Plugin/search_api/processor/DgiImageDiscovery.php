<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dgi_image_discovery\ImageDiscoveryInterface;
use Drupal\dgi_image_discovery\Plugin\search_api\processor\Property\DgiImageDiscoveryProperty;
use Drupal\node\NodeInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\user\Entity\User;

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
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ImageDiscoveryInterface $image_discovery,
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->imageDiscovery = $image_discovery;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
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
      $container->get('current_user')
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
      ];
      $properties['dgi_image_discovery'] = new DgiImageDiscoveryProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    // Save the current user.
    $current_user = $this->currentUser->getAccount();

    // Set the user to anonymous.
    $this->currentUser->setAccount(new AnonymousUserSession());

    $entity = $item->getOriginalObject()->getValue();
    $value = NULL;

    // Get the image discovery URL.
    if (!$entity->isNew() && $entity instanceof NodeInterface) {
      $event = $this->imageDiscovery->getImage($entity);
      $media = $event->getMedia();
      if (empty($media)) {
        return;
      }

      $media_source = $media->getSource();
      $file_id = $media_source->getSourceFieldValue($media);
      $image = $this->entityTypeManager->getStorage('file')->load($file_id);
      if (empty($image)) {
        return;
      }

      $fields = $item->getFields(FALSE);
      $fields = $this->getFieldsHelper()->filterForPropertyPath($fields, NULL, 'dgi_image_discovery');
      foreach ($fields as $field) {
        $config = $field->getConfiguration();
        $image_style = $config['image_style'];
        $value = $this->entityTypeManager->getStorage('image_style')->load($image_style)
          ->buildUrl($image->getFileUri());
        $field->addValue($value);
      }
    }

    // Restore the original user.
    $this->currentUser->setAccount(User::load($current_user->id()));
  }

}
