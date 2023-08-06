<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\blazy\Plugin\Field\FieldFormatter\BlazyFileFormatterBase;
use Drupal\slick\SlickDefault;

/**
 * Base class for slick image and file ER formatters.
 *
 * @todo extends BlazyFileSvgFormatterBase post blazy:2.17, or split.
 */
abstract class SlickFileFormatterBase extends BlazyFileFormatterBase {

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
  public static function defaultSettings() {
    return SlickDefault::imageSettings() + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function buildElements(array &$build, $files, $langcode) {
    $settings   = $this->formatter->toHashtag($build);
    $blazies    = $settings['blazies'];
    $tn_caption = $settings['thumbnail_caption'] ?? NULL;
    $tn_style   = $settings['thumbnail_style'] ?? NULL;
    $is_nav     = $blazies->is('nav') ?: $settings['nav'] ?? FALSE;
    $use_blazy  = $blazies->use('theme_thumbnail');

    foreach ($this->getElements($build, $files) as $element) {
      // Build individual item.
      $build['items'][] = $element;

      // Build individual thumbnail.
      if ($is_nav) {
        $sets = $this->formatter->toHashtag($element);
        $item = $this->formatter->toHashtag($element, 'item', NULL);
        $nav = ['#settings' => $sets];
        $caption = [];

        if ($tn_caption && $item && $text = $item->{$tn_caption} ?? NULL) {
          $caption = ['#markup' => Xss::filterAdmin($text)];
        }

        $tn = $tn_style
          ? $this->formatter->getThumbnail($sets, $item, $caption)
          : [];

        // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
        if ($use_blazy) {
          $nav = $tn;
        }
        else {
          // @todo remove at blazy:3.x to minimize more dups.
          $nav[static::$itemId] = $$tn;
          $nav[static::$captionId] = $caption;
        }

        $build[static::$navId]['items'][] = $nav;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginScopes(): array {
    $captions = ['title' => $this->t('Title'), 'alt' => $this->t('Alt')];

    return [
      'namespace'       => 'slick',
      'nav'             => TRUE,
      'thumb_captions'  => $captions,
      'thumb_positions' => TRUE,
    ] + parent::getPluginScopes();
  }

}
