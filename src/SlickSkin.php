<?php

namespace Drupal\slick;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements SlickSkinInterface.
 *
 * @todo deprecate at 3.x, and remove post 3.x.
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
