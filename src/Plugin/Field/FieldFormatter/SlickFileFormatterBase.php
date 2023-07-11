<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\blazy\Plugin\Field\FieldFormatter\BlazyFileFormatterBase;
use Drupal\slick\SlickDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for slick image and file ER formatters.
 */
abstract class SlickFileFormatterBase extends BlazyFileFormatterBase {

  use SlickFormatterTrait;

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
    return self::injectServices($instance, $container, 'image');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return SlickDefault::imageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entities = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($entities)) {
      return [];
    }

    return $this->commonViewElements($items, $langcode, $entities);
  }

  /**
   * {@inheritdoc}
   *
   * @todo use array for $caption_id post blazy:2.17.
   */
  public function buildElements(array &$build, $files, $langcode) {
    $settings   = SlickDefault::toHashtag($build);
    $blazies    = $settings['blazies'];
    $item_id    = $this->itemId;
    $caption_id = 'caption';
    $tn_caption = $settings['thumbnail_caption'] ?? NULL;
    $is_nav     = $blazies->is('nav') ?: $settings['nav'] ?? FALSE;
    $elements   = $this->getElements($build, $files, $caption_id);

    foreach ($elements as $element) {
      $sets = SlickDefault::toHashtag($element);
      $captions = $element[$caption_id] ?? [];

      // Do not pass captions to theme_blazy().
      unset($element[$caption_id]);

      // Image with responsive image, lazyLoad, and lightbox supports.
      $blazy = $this->formatter->getBlazy($element);
      $element[$item_id] = $blazy;

      // Build captions if so configured.
      $element[$caption_id] = $captions;

      // Build individual slick item.
      $build['items'][] = $element;

      // Build individual slick thumbnail.
      if ($is_nav) {
        $item = $element['item'];

        // Update with blazy processed settings such as unstyled extensions.
        $item_build = $blazy['#build'] ?? [];
        if ($blazysets = SlickDefault::toHashtag($item_build)) {
          $sets['blazies']->merge($blazysets['blazies']->storage());
        }

        $nav = ['settings' => $sets];

        // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
        $nav[$item_id] = empty($sets['thumbnail_style'])
          ? []
          : $this->formatter->getThumbnail($sets, $item);

        $markup = empty($item->{$tn_caption})
          ? []
          : ['#markup' => Xss::filterAdmin($item->{$tn_caption})];

        $nav[$caption_id] = $tn_caption ? $markup : [];

        $build['thumb']['items'][] = $nav;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getScopedFormElements() {
    $captions = ['title' => $this->t('Title'), 'alt' => $this->t('Alt')];

    return [
      'namespace'       => 'slick',
      'nav'             => TRUE,
      'thumb_captions'  => $captions,
      'thumb_positions' => TRUE,
    ] + parent::getScopedFormElements();
  }

}
