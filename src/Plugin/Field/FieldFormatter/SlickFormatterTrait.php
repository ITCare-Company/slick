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
    return array_merge($this->getCommonFieldDefinition(), $this->getSettings());
  }

  /**
   * Defines the common scope for both front and admin.
   */
  public function getCommonFieldDefinition() {
    $field = $this->fieldDefinition;
    return [
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
