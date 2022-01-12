<?php

namespace Drupal\dgi_image_discovery\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implementing this modules configuration form.
 */
class DIDForm extends ConfigFormBase {

  /**
   * Entity type manager class storage.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dgi_image_discovery.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'did_admin_settings';
  }

  /**
   * Load a Node or Term for the given type and id.
   *
   * @param string $type
   *   The storage type to load via entityTypeManager.
   * @param int|null $id
   *   The id of the node or term.
   *
   * @return mixed|Drupal\node\Entity\Node|Drupal\taxonomy\Entity\Term|null
   *   Null if no id present, or a Term or Node for the given type.
   */
  private function loadEntityByTypeAndId($type, $id) {
    if ($id) {
      return $this->entityTypeManager->getStorage($type)->load($id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dgi_image_discovery.adminsettings');

    $node = $this->loadEntityByTypeAndId('node', $config->get('dgi_image_discovery_default'));
    $term = $this->loadEntityByTypeAndId('taxonomy_term', $config->get('dgi_image_discovery_media_use'));

    $form['dgi_image_discovery_default'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Fallback image'),
      '#description' => $this->t('Specify the Node with the intended media bundle to use a source for a fallback image.'),
      '#default_value' => $node,
      '#target_type' => 'node',
      '#required' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['page', 'islandora_object', 'block_display'],
      ],
      '#weight' => '0',
    ];

    $form['dgi_image_discovery_media_use'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Media Use (Taxonomy) term'),
      '#description' => $this->t('Taxonomy term used to reference the media given the current bundle'),
      '#default_value' => $term,
      '#target_type' => 'taxonomy_term',
      '#required' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['islandora_media_use'],
      ],
      '#weight' => '1',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dgi_image_discovery.adminsettings')
      ->set('dgi_image_discovery_default', $form_state->getValue('dgi_image_discovery_default'))
      ->set('dgi_image_discovery_media_use', $form_state->getValue('dgi_image_discovery_media_use'))
      ->save();
  }

}
