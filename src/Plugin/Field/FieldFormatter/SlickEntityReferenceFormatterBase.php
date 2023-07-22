<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\blazy\Field\BlazyEntityReferenceBase;
use Drupal\blazy\Field\BlazyField;
use Drupal\slick\SlickDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for slick entity reference formatters with field details.
 *
 * @see \Drupal\slick_media\Plugin\Field\FieldFormatter
 * @see \Drupal\slick_paragraphs\Plugin\Field\FieldFormatter
 */
abstract class SlickEntityReferenceFormatterBase extends BlazyEntityReferenceBase {

  use SlickFormatterTrait;

  /**
   * {@inheritdoc}
   */
  protected static $namespace = 'slick';

  /**
   * {@inheritdoc}
   */
  protected static $itemId = 'slide';

  /**
   * {@inheritdoc}
   */
  protected static $itemPrefix = 'slide';

  /**
   * {@inheritdoc}
   */
  protected static $captionId = 'caption';

  /**
   * {@inheritdoc}
   */
  protected static $navId = 'thumb';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return self::injectServices($instance, $container, 'entity');
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
  public function buildElementThumbnail(array &$build, $element, $entity, $delta) {
    // The settings in $element has updated metadata extracted from media.
    $settings  = $this->formatter->toHashtag($element);
    $blazies   = $settings['blazies'];
    $view_mode = $settings['view_mode'] ?? '';
    $caption   = $settings['thumbnail_caption'] ?? NULL;
    $tn_style  = $settings['thumbnail_style'] ?? NULL;
    $item      = $this->formatter->toHashtag($element, 'item', NULL);
    $is_nav    = $blazies->is('nav') ?: $settings['nav'] ?? FALSE;

    if ($is_nav) {
      // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
      $element[static::$itemId] = $tn_style
        ? $this->formatter->getThumbnail($settings, $item) : [];

      $element[static::$captionId] = $caption
        ? BlazyField::view($entity, $caption, $view_mode)
        : [];

      $build[static::$navId]['items'][$delta] = $element;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginScopes(): array {
    $_texts = ['text', 'text_long', 'string', 'string_long', 'link'];
    $texts  = $this->getFieldOptions($_texts);

    return [
      'thumb_captions'  => $texts,
      'thumb_positions' => TRUE,
      'nav'             => TRUE,
    ] + parent::getPluginScopes();
  }

}
