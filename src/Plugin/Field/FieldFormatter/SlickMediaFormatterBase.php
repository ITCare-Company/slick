<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\blazy\BlazyEntity;
use Drupal\blazy\Plugin\Field\FieldFormatter\BlazyMediaFormatterBase;
use Drupal\slick\SlickDefault;
use Drupal\slick\SlickFormatterInterface;
use Drupal\slick\SlickManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Slick media formatters with field details, and oEmbed support.
 *
 * @see Drupal\slick\Plugin\Field\FieldFormatter\SlickMediaFormatter
 */
abstract class SlickMediaFormatterBase extends BlazyMediaFormatterBase implements ContainerFactoryPluginInterface {

  use SlickFormatterTrait;

  /**
   * Constructs a SlickMediaFormatter object.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, ImageFactory $image_factory, BlazyEntity $blazy_entity, SlickFormatterInterface $formatter, SlickManagerInterface $manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $image_factory, $blazy_entity, $formatter);
    $this->formatter = $formatter;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('image.factory'),
      $container->get('blazy.entity'),
      $container->get('slick.formatter'),
      $container->get('slick.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return SlickDefault::extendedSettings() + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function getScopedFormElements() {
    $multiple = $this->fieldDefinition->getFieldStorageDefinition()->isMultiple();

    return [
      'namespace' => 'slick',
      'grid_form' => $multiple,
      'style' => $multiple,
    ] + parent::getScopedFormElements();
  }

}
