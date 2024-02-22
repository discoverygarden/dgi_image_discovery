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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $configuration = $field->getConfiguration();

    // Get an array of image styles.
    $image_styles_array = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple();
    $image_styles = [];

    if (!empty($image_styles_array)) {
      foreach ($image_styles_array as $id => $style) {
        $image_styles[$id] = $style->label();
      }
    }

    $form['image_style'] = [
      '#type' => 'select',
      '#options' => $image_styles,
      '#title' => $this->t('Image Style'),
      '#description' => $this->t('Select the image style that should be applied to derive the DGI Image Discovery image url.'),
      '#default_value' => $configuration['image_style'] ?? $this->defaultConfiguration()['image_style'],
    ];

    return $form;
  }

}
