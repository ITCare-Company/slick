<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\blazy\Plugin\Field\FieldFormatter\BlazyTextFormatter;
use Drupal\slick\SlickDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Slick Text' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_text",
 *   label = @Translation("Slick Text"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   },
 *   quickedit = {"editor" = "disabled"}
 * )
 */
class SlickTextFormatter extends BlazyTextFormatter {

  use SlickFormatterTrait {
    buildSettings as traitBuildSettings;
  }

  /**
   * {@inheritdoc}
   */
  protected $namespace = 'slick';

  /**
   * {@inheritdoc}
   */
  protected $itemId = 'slide';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return self::injectServices($instance, $container, 'text');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return SlickDefault::baseSettings() + SlickDefault::gridSettings();
  }

  /**
   * Build the slick carousel elements.
   */
  public function buildElements(array &$build, $items, $langcode) {
    foreach ($this->getElements($items) as $element) {
      $build['items'][] = $element;
    }
  }

  /**
   * Builds the settings.
   */
  public function buildSettings() {
    return ['vanilla' => TRUE] + $this->traitBuildSettings();
  }

}
