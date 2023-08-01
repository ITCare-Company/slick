<?php

namespace Drupal\slick;

use Drupal\slick\Entity\Slick;
use Drupal\blazy\BlazyFormatter;

/**
 * Provides Slick field formatters utilities.
 */
class SlickFormatter extends BlazyFormatter implements SlickFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function buildSettings(array &$build, $items) {
    $this->hashtag($build);

    $settings = &$build['#settings'];
    $this->verify($settings);

    $blazies = $settings['blazies'];
    $config  = $settings['slicks'];

    // Prepare integration with Blazy.
    $settings['_unload'] = FALSE;

    // @todo move it into self::preSettingsData() post Blazy 2.10.
    $optionset = Slick::verifyOptionset($build, $settings['optionset']);

    // Prepare integration with Blazy.
    $blazies->set('initial', $optionset->getSetting('initialSlide') ?: 0);

    // Only display thumbnail nav if having at least 2 slides. This might be
    // an issue such as for ElevateZoomPlus module, but it should work it out.
    $nav = $blazies->isset('nav') || isset($settings['nav']);
    if (!$nav) {
      $nav = !empty($settings['optionset_thumbnail']) && isset($items[1]);
    }

    // Nothing to work with Vanilla on, disable the asnavfor, else JS error.
    $nav = $nav && empty($settings['vanilla']);

    // Dups to allow one swap to all sliders as seen at ElevateZoomPlus.
    $settings['nav'] = $nav;
    $blazies->set('is.nav', $nav);
    $config->set('is.nav', $nav);

    // Do not bother for SlickTextFormatter, or when vanilla is on.
    if (empty($settings['vanilla'])) {
      $optionset->whichLazy($settings);
    }

    // Pass basic info to parent::buildSettings().
    parent::buildSettings($build, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function preBuildElements(array &$build, $items, array $entities = []) {
    parent::preBuildElements($build, $items, $entities);

    $this->hashtag($build);
    $settings = &$build['#settings'];
    $this->verify($settings);

    // Only trim overridables options if disabled.
    if (empty($settings['override']) && isset($settings['overridables'])) {
      $settings['overridables'] = array_filter($settings['overridables']);
    }

    $this->moduleHandler->alter('slick_settings', $build, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function verify(array &$settings): void {
    parent::verify($settings);

    SlickDefault::verify($settings, $this);
  }

}
