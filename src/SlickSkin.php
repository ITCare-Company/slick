<?php

namespace Drupal\slick;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides deprecated skins.
 *
 * @todo remove after/ along with slick_extras:3.0.0 release.
 *
 * @deprecated in slick:8.x-2.0 and is removed from slick:3.0.0. Use
 * Drupal\slick\SlickSkinPluginBase instead.
 * @see https://www.drupal.org/node/3105648
 */
class SlickSkin implements SlickSkinInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function skins() {
    return \Drupal::service('slick.skin_manager')->load('slick_skin')->skins();
  }

}
