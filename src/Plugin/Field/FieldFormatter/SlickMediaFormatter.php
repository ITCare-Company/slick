<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'slick media' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_media",
 *   label = @Translation("Slick Media"),
 *   description = @Translation("Display the referenced entities as a Slick carousel."),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class SlickMediaFormatter extends SlickEntityReferenceFormatterBase {

  /**
   * Returns the blazy manager.
   */
  public function blazyManager() {
    return $this->formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entities = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($entities)) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = $this->buildSettings();

    // Sets dimensions once to reduce method ::transformDimensions() calls.
    // @todo: A more flexible way to also support paragraphs at one go.
    $entities = array_values($entities);
    if (!empty($settings['image_style']) && ($entities[0]->getEntityTypeId() == 'media' && $entities[0]->hasField('thumbnail'))) {
      $item = $entities[0]->get('thumbnail')->first();
      $settings['first_item'] = $item;
      $settings['first_uri'] = $item->entity->getFileUri();
    }

    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $entities, $langcode);

    return $this->manager()->build($build);
  }

  /**
   * Builds the settings.
   */
  public function buildSettings() {
    $settings = parent::buildSettings();
    $settings['blazy'] = TRUE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getScopedFormElements() {
    $multiple = $this->fieldDefinition->getFieldStorageDefinition()->isMultiple();

    return [
      'grid_form' => $multiple,
      'style'     => $multiple,
    ] + parent::getScopedFormElements();
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();
    return $storage->isMultiple() && $storage->getSetting('target_type') === 'media';
  }

}
