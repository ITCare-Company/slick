<?php

namespace Drupal\slick;

use Drupal\blazy\Blazy;
use Drupal\blazy\BlazyDefault;

/**
 * Defines shared plugin default settings for field formatter and Views style.
 *
 * @see FormatterBase::defaultSettings()
 * @see StylePluginBase::defineOptions()
 */
class SlickDefault extends BlazyDefault {

  /**
   * {@inheritdoc}
   */
  public static function baseSettings() {
    return [
      'optionset'       => 'default',
      'override'        => FALSE,
      'overridables'    => [],
      'skin'            => '',
      'skin_arrows'     => '',
      'skin_dots'       => '',
      'use_theme_field' => FALSE,
    ] + parent::baseSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function gridSettings() {
    return [
      'preserve_keys' => FALSE,
      'visible_items' => 0,
    ] + parent::gridSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function imageSettings() {
    return [
      'optionset_thumbnail' => '',
      'skin_thumbnail'      => '',
      'thumbnail_caption'   => '',
      'thumbnail_effect'    => '',
      'thumbnail_position'  => '',
    ] + self::baseSettings()
      + self::gridSettings()
      + parent::imageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function extendedSettings() {
    return [
      'thumbnail' => '',
      // @todo remove post 2.10.
      'use_theme_blazy' => FALSE,
    ] + self::imageSettings() + parent::extendedSettings();
  }

  /**
   * Returns filter settings.
   */
  public static function filterSettings() {
    $settings = self::imageSettings();
    $unused = [
      'breakpoints' => [],
      'sizes'       => '',
      'grid_header' => '',
    ];
    foreach ($unused as $key => $value) {
      if (isset($settings[$key])) {
        unset($settings[$key]);
      }
    }
    return $settings + self::gridSettings();
  }

  /**
   * Returns Slick specific settings.
   */
  public static function slicks() {
    return [
      'breaking'      => FALSE,
      'display'       => 'main',
      'library'       => 'slick',
      // 'nav'           => FALSE,
      // 'navpos'        => FALSE,
      'thumbnail_uri' => '',
      'unslick'       => FALSE,
      'vanilla'       => FALSE,
      'vertical'      => FALSE,
      'vertical_tn'   => FALSE,
    ];
  }

  /**
   * Returns HTML or layout related settings to shut up notices.
   *
   * @return array
   *   The default settings.
   */
  public static function htmlSettings() {
    $items = [];
    foreach (self::slicks() as $key => $value) {
      if (is_bool($value)) {
        $items['is'][$key] = $value;
      }
      else {
        $items[$key] = $value;
      }
    }

    return [
      'slicks'    => Blazy::settings($items),
      'item_id'   => 'slide',
      'namespace' => 'slick',
      // @todo remove `+ self::slicks()`.
    ] + self::slicks()
      + self::imageSettings()
      + parent::htmlSettings();
  }

  /**
   * Defines JS options required by theme_slick(), used with optimized option.
   */
  public static function jsSettings() {
    return [
      'asNavFor'        => '',
      'downArrowTarget' => '',
      'downArrowOffset' => '',
      'lazyLoad'        => 'ondemand',
      'prevArrow'       => 'Previous',
      'nextArrow'       => 'Next',
      'pauseIcon'       => 'slick-pause-icon',
      'playIcon'        => 'slick-play-icon',
      'rows'            => 1,
      'slidesPerRow'    => 1,
      'slide'           => '',
      'slidesToShow'    => 1,
      'vertical'        => FALSE,
    ];
  }

  /**
   * Returns slick theme properties.
   */
  public static function themeProperties() {
    return [
      'attached' => [],
      'attributes' => [],
      'items' => [],
      'options' => [],
      'optionset' => NULL,
      'settings' => [],
    ];
  }

  /**
   * Returns svg-related field formatter settings.
   *
   * @todo remove post blazy:2.17.
   */
  public static function svgSettings() {
    return [
      'svg_inline' => FALSE,
      'svg_fill' => FALSE,
      'svg_sanitize' => TRUE,
      'svg_sanitize_remote' => FALSE,
      'svg_hide_caption' => FALSE,
      'svg_attributes' => '',
    ];
  }

  /**
   * Verify the settings.
   */
  public static function verify(array &$settings, $manager): void {
    $config = $settings['slicks'] ?? NULL;
    if (!$config) {
      $settings += self::htmlSettings();
      $config = $settings['slicks'];
    }

    if (!$config->get('ui')) {
      $ui = $manager->configMultiple('slick.settings');
      $config->set('ui', $ui);
    }
  }

}
