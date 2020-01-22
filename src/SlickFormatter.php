<?php

namespace Drupal\slick;

use Drupal\slick\Entity\Slick;
use Drupal\blazy\BlazyFormatterManager;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Implements SlickFormatterInterface.
 *
 * @todo use BlazyFormatter post release Blazy 2.x.
 */
class SlickFormatter extends BlazyFormatterManager implements SlickFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function buildSettings(array &$build, $items) {
    $settings = &$build['settings'];

    // Prepare integration with Blazy.
    $settings['item_id']   = 'slide';
    $settings['namespace'] = 'slick';

    // Pass basic info to parent::buildSettings().
    parent::buildSettings($build, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function preBuildElements(array &$build, $items, array $entities = []) {
    parent::preBuildElements($build, $items, $entities);

    $settings = &$build['settings'];

    // Slick specific stuffs.
    $build['optionset'] = Slick::loadWithFallback($settings['optionset']);

    // Only display thumbnail nav if having at least 2 slides. This might be
    // an issue such as for ElevateZoom Plus module, but it should work it out.
    if (!isset($settings['nav'])) {
      $settings['nav'] = !empty($settings['optionset_thumbnail']) && isset($items[1]);
    }

    // Do not bother for SlickTextFormatter, or when vanilla is on.
    if (empty($settings['vanilla'])) {
      $lazy              = $build['optionset']->getSetting('lazyLoad');
      $settings['blazy'] = $lazy == 'blazy' || !empty($settings['blazy']);
      $settings['lazy']  = $settings['blazy'] ? 'blazy' : $lazy;

      // Allows Blazy to take over for advanced features like Responsive image,
      // CSS background, video, etc.
      if (empty($settings['blazy'])) {
        $settings['lazy_class'] = $settings['lazy_attribute'] = 'lazy';
      }

      // Disable anything lazy-related settings if in preview mode.
      $settings['lazy'] = empty($settings['is_preview']) ? $settings['lazy'] : '';
    }
    else {
      // Nothing to work with Vanilla on, disable the asnavfor, else JS error.
      $settings['nav'] = FALSE;
    }

    // Only trim overridables options if disabled.
    if (empty($settings['override']) && isset($settings['overridables'])) {
      $settings['overridables'] = array_filter($settings['overridables']);
    }

    $this->getModuleHandler()->alter('slick_settings', $build, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnail(array $settings = [], $item = NULL) {
    if (!empty($settings['uri'])) {
      return [
        '#theme'      => 'image_style',
        '#style_name' => empty($settings['thumbnail_style']) ? 'thumbnail' : $settings['thumbnail_style'],
        '#uri'        => $settings['uri'],
        '#item'       => $item,
        '#alt'        => $item instanceof ImageItem ? $item->getValue()['alt'] : '',
      ];
    }
    return [];
  }

}
