<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * A Trait common for slick formatters.
 */
trait SlickFormatterTrait {

  /**
   * The slick field formatter manager.
   *
   * @var \Drupal\slick\SlickManagerInterface
   */
  protected $manager;

  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   * @todo deprecated in blazy:8.x-2.0 and is removed from blazy:8.x-3.0.
   */
  protected $imageFactory = NULL;

  /**
   * Returns the image factory.
   *
   * @todo deprecated in blazy:8.x-2.0 and is removed from blazy:8.x-3.0. Use
   *   BlazyOEmbed::imageFactory() instead.
   * @see https://www.drupal.org/node/3103018
   */
  public function imageFactory() {
    if (is_null($this->imageFactory)) {
      $this->imageFactory = \Drupal::service('image.factory');
    }
    return $this->imageFactory;
  }

  /**
   * Returns the slick field formatter service.
   */
  public function formatter() {
    return $this->formatter;
  }

  /**
   * Returns the slick service.
   */
  public function manager() {
    return $this->manager;
  }

  /**
   * Returns the slick admin service shortcut.
   */
  public function admin() {
    return \Drupal::service('slick.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->admin()->getSettingsSummary($this->getScopedFormElements());
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getFieldStorageDefinition()->isMultiple();
  }

  /**
   * Builds the settings.
   */
  public function buildSettings() {
    $settings = array_merge($this->getCommonFieldDefinition(), $this->getSettings());
    $settings['third_party'] = $this->getThirdPartySettings();
    return $settings;
  }

  /**
   * Defines the common scope for both front and admin.
   */
  public function getCommonFieldDefinition() {
    $field = $this->fieldDefinition;
    return [
      'namespace'         => 'slick',
      'current_view_mode' => $this->viewMode,
      'field_name'        => $field->getName(),
      'field_type'        => $field->getType(),
      'entity_type'       => $field->getTargetEntityTypeId(),
      'plugin_id'         => $this->getPluginId(),
      'target_type'       => $this->getFieldSetting('target_type'),
    ];
  }

  /**
   * Defines the common scope for the form elements.
   */
  public function getCommonScopedFormElements() {
    return ['settings' => $this->getSettings()] + $this->getCommonFieldDefinition();
  }

}
