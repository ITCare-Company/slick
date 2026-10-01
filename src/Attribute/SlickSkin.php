<?php

declare(strict_types=1);

namespace Drupal\slick\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a SlickSkin attribute object.
 *
 * @see \Drupal\slick\Annotation\SlickSkin
 * @see \Drupal\slick\SlickSkinManager
 * @see plugin_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class SlickSkin extends Plugin {

  /**
   * Constructs a SlickSkin attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The label of the plugin.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
