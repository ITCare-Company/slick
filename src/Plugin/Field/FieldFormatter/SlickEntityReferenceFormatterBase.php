<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
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
  protected static $fieldType = 'entity';

  /**
   * {@inheritdoc}
   *
   * @todo remove post blazy:2.17, no differences so far.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return static::injectServices($instance, $container, static::$fieldType);
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
  protected function buildElementThumbnail(array &$build, array $element, $entity, $delta) {
    // The settings in $element has updated metadata extracted from media.
    $settings  = $this->formatter->toHashtag($element);
    $blazies   = $settings['blazies'];
    $is_nav    = $blazies->is('nav') || !empty($settings['nav']);
    $item      = $this->formatter->toHashtag($element, 'item', NULL);
    $view_mode = $settings['view_mode'] ?? '';
    $_caption  = $settings['thumbnail_caption'] ?? NULL;
    $_style    = $settings['thumbnail_style'] ?? NULL;
    $use_blazy = $blazies->use('theme_thumbnail');
    $caption   = [];

    // @todo recheck any other places calling this method, and remove this.
    if (!$is_nav) {
      return;
    }

    if ($_caption) {
      if ($item && $text = trim($item->{$_caption} ?? '')) {
        $caption = ['#markup' => Xss::filterAdmin($text)];
      }
      else {
        $caption = BlazyField::view($entity, $_caption, $view_mode);
      }
    }

    $tn = $_style
      ? $this->formatter->getThumbnail($settings, $item, $caption)
      : [];

    // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
    if ($use_blazy) {
      $element = $tn;
    }
    else {
      // @todo remove at blazy:3.x to minimize more dups.
      $element[static::$itemId] = $tn;
      $element[static::$captionId] = $caption;
    }

    $build[static::$navId]['items'][$delta] = $element;
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
