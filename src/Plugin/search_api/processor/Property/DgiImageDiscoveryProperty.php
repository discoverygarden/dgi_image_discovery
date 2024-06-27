<?php

namespace Drupal\dgi_image_discovery\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;

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
      'url_generator' => 'pre_generated',
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
    $form['url_generator'] = [
      '#type' => 'select',
      '#options' => $this->definition['dgi_image_discovery__url_generator_options'],
      '#title' => $this->t('URL Generator'),
      '#default_value' => $configuration['url_generator'],
    ];

    return $form;
  }

}
