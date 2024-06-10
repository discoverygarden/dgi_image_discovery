<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;
use Drupal\search_api\Utility\Utility;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Defines a "DGI Image Discovery" property.
 */
class DgiImageDiscoveryProperty extends ConfigurablePropertyBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'image_style' => 'solr_grid_thumbnail',
      'roles' => [AccountInterface::ANONYMOUS_ROLE],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration = $field->getConfiguration() + $this->defaultConfiguration();

    $form['image_style'] = [
      '#type' => 'select',
      '#options' => image_style_options(FALSE),
      '#title' => $this->t('Image Style'),
      '#description' => $this->t('Select the image style that should be applied to derive the DGI Image Discovery image url.'),
      '#default_value' => $configuration['image_style'],
    ];

    // XXX: Copypasta from https://git.drupalcode.org/project/search_api/-/blob/8.x-1.x/src/Plugin/search_api/processor/Property/RenderedItemProperty.php?ref_type=heads#L41-52
    $roles = array_map(function (RoleInterface $role) {
      return Utility::escapeHtml($role->label());
    }, Role::loadMultiple());
    $form['roles'] = [
      '#type' => 'select',
      '#title' => $this->t('User roles'),
      '#description' => $this->t('Your item will be rendered as seen by a user with the selected roles. We recommend to just use "@anonymous" here to prevent data leaking out to unauthorized roles.', ['@anonymous' => $roles[AccountInterface::ANONYMOUS_ROLE]]),
      '#options' => $roles,
      '#multiple' => TRUE,
      '#default_value' => $configuration['roles'],
      '#required' => TRUE,
    ];

    return $form;
  }

}
