<?php

namespace Drupal\slick\Plugin\Filter;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\blazy\Blazy;
use Drupal\blazy\BlazyUtil;
use Drupal\blazy\Plugin\Filter\BlazyFilter;
use Drupal\slick\SlickDefault;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter for a Slick.
 *
 * Best after Blazy, Align images, caption images.
 *
 * @Filter(
 *   id = "slick_filter",
 *   title = @Translation("Slick"),
 *   description = @Translation("Creates slideshow/ carousel with Slick shortcode."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   settings = {
 *     "optionset" = "default",
 *     "media_switch" = "",
 *   },
 *   weight = 4
 * )
 *
 * @todo replace methods by Drupal\blazy\Plugin\Filter\BlazyFilterUtil post 2.5.
 * @todo use Drupal\blazy\Plugin\Filter\BlazyFilterBase instead post 2.5.
 */
class SlickFilter extends BlazyFilter {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $instance->admin = $container->get('slick.admin');
    $instance->manager = $container->get('slick.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'settings' => array_merge($this->pluginDefinition['settings'], SlickDefault::filterSettings()),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (empty($text) || stristr($text, '[slick') === FALSE) {
      return $result;
    }

    $attachments = [];
    $settings = $this->buildSettings($text);
    $text = self::unwrap($text, 'slick', 'slide');
    $dom = Html::load($text);
    $nodes = self::getValidNodes($dom, ['slick']);

    if (count($nodes) > 0) {
      foreach ($nodes as $node) {
        $output = $this->build($node, $settings);

        if (empty($output)) {
          continue;
        }

        $altered_html = $this->manager->getRenderer()->render($output);

        // Load the altered HTML into a new DOMDocument, retrieve element.
        $updated_nodes = Html::load($altered_html)->getElementsByTagName('body')
          ->item(0)
          ->childNodes;

        foreach ($updated_nodes as $updated_node) {
          // Import the updated from the new DOMDocument into the original
          // one, importing also the child nodes of the updated node.
          $updated_node = $dom->importNode($updated_node, TRUE);
          $node->parentNode->insertBefore($updated_node, $node);
        }

        // Finally, remove the original node.
        if ($node->parentNode) {
          $node->parentNode->removeChild($node);
        }
      }

      $all = self::attach($settings);
      $attachments = $this->manager->attach($all);
    }

    // Attach Blazy component libraries.
    $result->setProcessedText(Html::serialize($dom))
      ->addAttachments($attachments);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function buildSettings($text) {
    $this->settings['no_item_container'] = TRUE;
    $settings = parent::buildSettings($text);

    $settings['plugin_id'] = $this->getPluginId();

    // Provides alter like formatters to modify at one go, even clumsy here.
    $build = ['settings' => $settings];
    $this->manager->getModuleHandler()->alter('slick_settings', $build, $this->settings);
    return array_merge($settings, $build['settings']);
  }

  /**
   * Build the slick.
   */
  private function build($object, array $settings) {
    $attribute = $object->getAttribute('data');

    $settings['id'] = $settings['gallery_id'] = Blazy::getHtmlId(str_replace('_', '-', $settings['plugin_id']));

    if (!empty($attribute) && mb_strpos($attribute, ":") !== FALSE) {
      return $this->byEntity($attribute, $settings);
    }

    return $this->byDom($object, $settings);
  }

  /**
   * Build the slick using the node ID and field_name.
   */
  private function byEntity($attribute, array $settings) {
    list($entity_type, $id, $field_name, $field_image) = array_pad(array_map('trim', explode(":", $attribute, 4)), 4, NULL);
    if (empty($field_name)) {
      return [];
    }

    $entity = $this->manager->entityLoad($id, $entity_type);
    $settings['entity_type_id'] = $entity_type;
    $settings['entity_id'] = $id;
    $settings['field_name'] = $field_name;
    $settings['image'] = $field_image;

    if ($entity && $entity->hasField($field_name)) {
      $settings['bundle'] = $entity->bundle();
      $list = $entity->get($field_name);

      if ($list) {
        $definition = $list->getFieldDefinition();
        $field_type = $definition->get('field_type');
        $field_settings = $definition->get('settings');
        $handler = isset($field_settings['handler']) ? $field_settings['handler'] : NULL;
        $texts = ['text', 'text_long', 'text_with_summary'];

        $formatter = NULL;
        // @todo refine for main stage, etc.
        if ($field_type == 'entity_reference') {
          if ($handler == 'default:media') {
            $formatter = 'slick_media';
          }
          else {
            // @todo refine for Paragraphs, etc.
            $settings['vanilla'] = TRUE;
            $formatter = 'slick_entityreference';
          }
        }
        elseif ($field_type == 'image') {
          $formatter = 'slick_image';
        }
        elseif (in_array($field_type, $texts)) {
          $formatter = 'slick_text';
        }

        if ($formatter) {
          return $list->view([
            'type' => $formatter,
            'settings' => $settings,
          ]);
        }
      }
    }

    return [];
  }

  /**
   * Build the slick using the DOM lookups.
   */
  private function byDom($object, array $settings) {
    $text = self::getHtml($object);
    if (empty($text)) {
      return [];
    }

    $dom = Html::load($text);
    $nodes = $this->getNodes($dom);
    if ($nodes->length == 0) {
      return [];
    }

    $build = ['settings' => $settings];
    $this->prepareBuild($build, $object);
    if (!isset($settings['nav'])) {
      $build['settings']['nav'] = !empty($settings['optionset_thumbnail']) && $nodes->length > 1;
    }

    foreach ($nodes as $delta => $node) {
      if (!($node instanceof \DOMElement)) {
        continue;
      }

      $sets = $build['settings'];
      $sets['delta'] = $delta;
      $sets['thumbnail_uri'] = $node->getAttribute('data-thumb');
      $element = ['caption' => NULL, 'item' => NULL, 'settings' => $sets];

      $this->buildItem($element, $node);

      if (empty($element['slide'])) {
        $element['slide'] = ['#markup' => $dom->saveHtml($node)];
      }

      $build['items'][$delta] = $element;

      // Build individual slick thumbnail.
      if (!empty($sets['nav'])) {
        $this->buildNav($build, $element);
      }
    }

    return $this->manager->build($build);
  }

  /**
   * Build the slide item.
   */
  private function buildItem(array &$element, $node) {
    $children = $node->getElementsByTagName('img');
    if ($children->length == 0) {
      $children = $node->getElementsByTagName('iframe');
    }

    if ($children->length > 0) {
      $child = $children->item(0);

      // Provides individual item settings.
      $this->buildItemSettings($element, $child);

      // Extracts image item from SRC attribute.
      $this->buildImageItem($element, $child);

      // Extracts image caption if available.
      $this->buildImageCaption($element, $child);

      if (!empty($element['settings']['uri'])) {
        $element['slide'] = $this->blazyManager->getBlazy($element);
      }
    }

    if ($attributes = self::getAttribute($node)) {
      $element['attributes'] = $attributes;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildImageCaption(array &$build, &$node) {
    $item = parent::buildImageCaption($build, $node);

    if (!empty($build['captions'])) {
      $build['caption'] = $build['captions'];
      unset($build['captions']);
    }
    return $item;
  }

  /**
   * Prepares the slick.
   */
  private function prepareBuild(array &$build, $object) {
    $settings = &$build['settings'];
    $options = [];
    if ($check = $object->getAttribute('options')) {
      $check = str_replace("'", '"', $check);
      $options = Json::decode($check);
    }
    if ($check = $object->getAttribute('settings')) {
      $check = str_replace("'", '"', $check);
      $check = Json::decode($check);
      $settings = array_merge($settings, $check);
    }

    $build['options'] = $options;
  }

  /**
   * Build the slick navigation.
   */
  private function buildNav(array &$build, array $element) {
    $sets = $element['settings'];
    $item = $element['item'];
    $delta = $sets['delta'];
    $caption = empty($sets['thumbnail_caption']) ? NULL : $sets['thumbnail_caption'];
    $text = (empty($item) || empty($item->{$caption})) ? [] : ['#markup' => Xss::filterAdmin($item->{$caption})];

    // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
    $thumb = [
      'settings' => $sets,
      'slide' => $this->manager->getThumbnail($sets, $item),
      'caption' => $text,
    ];

    $build['thumb']['items'][$delta] = $thumb;
    unset($thumb);
  }

  /**
   * Returns DOMElement nodes expected to be slide items.
   */
  private function getNodes($dom) {
    $xpath = new \DOMXPath($dom);

    return $xpath->query("//slide");
  }

  /**
   * Returns the expected caption DOMelement.
   */
  protected function getCaptionElement($node) {
    $caption = NULL;
    // @todo remove check post Blazy 2.5+.
    if (method_exists(get_parent_class($this), 'getCaptionElement')) {
      $caption = parent::getCaptionElement($node);
    }

    // @todo figure out better traversal with DOM.
    if (empty($caption) && $node->parentNode) {
      $parent = $node->parentNode->parentNode;
      if ($parent && $grandpa = $parent->parentNode) {
        if ($grandpa->parentNode) {
          $divs = $grandpa->parentNode->getElementsByTagName('div');
        }
        else {
          $divs = $grandpa->getElementsByTagName('div');
        }

        if ($divs) {
          foreach ($divs as $div) {
            $class = $div->getAttribute('class');
            if ($class == 'blazy__caption') {
              $caption = $div;
              break;
            }
          }
        }
      }
    }
    return $caption;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t("
        <p><b>Slick</b>: Create a slideshow/ carousel with a shortcode. Pay attention to attributes, backslash, single and double quotes:</p>
        <ol>
          <li><b>Basic</b>, with inline HTML: <br><code>[slick]...[slide]...[/slide]...[/slick]</code></li>
          <li><b>With self-closing <code>data=ENTITY_TYPE:ID:FIELD_NAME:FIELD_IMAGE</code></b>, without inline HTML: <br><code>[slick data=\"node:44:field_media\" /]</code><br>
          <code>[slick data=\"node:44:field_media:field_media_image\" /]</code><br>
          <b>Required</b>: <code>ENTITY_TYPE:ID:FIELD_NAME</code>, where <code>ENTITY_TYPE</code> is <b>node</b> -- only tested with node, <code>ID</code> is <b>node ID</b>, <code>FIELD_NAME</code> can be field Media, Entityreference, Image, Text (long or with summary), must be multi-value, or unlimited. <br><b>Optional</b>: <code>FIELD_IMAGE</code> named <code>field_media_image</code> as found at Media Image/ Video for hires poster image, must be similar and single-value field image for all media entities to have mixed media correctly.</li>
          <li><b>With settings and or options</b>, to override Slick filter settings: <br><code>[slick settings=\"{}\" options=\"{}\"]...[slide]...[/slide]...[/slick]</code><br>Where <code>settings</code> is HTML settings as seen at Filter, Field or Views UI forms, and <code>options</code> is JavaScript options as seen at Optionset UI forms.</li>
          <li><b>Options only</b>: any JavaScript options relevant from <code>slick/config/install/slick.optionset.default.yml</code>:<br>
            <code>[slick options=\"{'type':  'loop', 'arrows': false, 'pagination': true}\"]...[/slick]</code>
          </li>
          <li><b>HTML settings only</b>: any HTML settings relevant from <code>SlickDefault/ BlazyDefault</code> methods:<br>
             <code>[slick settings=\"{'optionset': 'x_main', 'skin': 'classic', 'layout': 'bottom'}\"]...[/slick]</code>
          </li>
        </ol>
        <p><br><b>Tips</b>, if any issues:</p>
        <ul>
          <li>Attributes <code>data, settings, options</code> can be put together into one <code>[slick]</code>.</li>
          <li><code>[slide]</code> can have any valid attributes, e.g.: <br><code>[slide class=\"slide--custom-class\"]...[/slide]</code>. And these will be retained in the actual slide element.</li>
          <li>Except for self-closing one-liner <code>data</code> attribute, be sure slide items are stacked, separated by line breaks, or any relevant HTML tags, and wrapped each with <code>[slide]</code>:<br>
            <code>
              [slick]<br>
                &nbsp;&nbsp;[slide]<br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;IMG&gt;<br>&nbsp;&nbsp;[/slide]<br>
                &nbsp;&nbsp;[slide]<br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;IFRAME&gt;<br>&nbsp;&nbsp;[/slide]<br>
                &nbsp;&nbsp;[slide]<br>&nbsp;&nbsp;&nbsp;&nbsp;&lt;p&gt;Any non-media HTML content&lt;/p&gt;<br>&nbsp;&nbsp;[/slide]<br>
              [/slick]
            </code><br>
            <code>IMG/ IFRAME</code>, or other HTML as slide contents can be wrapped with any relevant tags, no problem.
            </li>
          <li>Except for <code>[slide]</code>, avoid using the reserved square bracket characters <code>[</code> and <code>]</code> or other inner shortcodes inside <code>[slick]...[/slick]</code> blocks till we support nested slicks.</li>
        </ul>");
    }
    else {
      return $this->t('<b>Slick</b>: Create a slideshow/ carousel: <br><ul><li><b>With self-closing using data entity, <code>data=ENTITY_TYPE:ID:FIELD_NAME:FIELD_IMAGE</code></b>:<br><code>[slick data="node:44:field_media" /]</code>. <code>FIELD_IMAGE</code> is optional.</li><li><b>With any HTML</b>: <br><code>[slick settings="{}" options="{}"]...[slide]...[/slide]...[/slick]</li></code></ul>');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $definition = [
      'settings' => $this->settings,
      'background' => TRUE,
      'caches' => FALSE,
      'image_style_form' => TRUE,
      'media_switch_form' => TRUE,
      'multimedia' => TRUE,
      'thumb_captions' => 'default',
      'thumb_positions' => TRUE,
      'nav' => TRUE,
    ];

    $element = [];
    $this->admin->buildSettingsForm($element, $definition);

    if (isset($element['media_switch'])) {
      unset($element['media_switch']['#options']['content']);
    }

    if (isset($element['closing'])) {
      $element['closing']['#suffix'] = $this->t('Best after Blazy, Align / Caption images filters -- all are not required to function. Not tested against, nor dependent on, Shortcode module. Be sure to place Slick filter before any other Shortcode if installed.');
    }

    return $element;
  }

  /**
   * Unwrap the enclosing tags.
   *
   * @todo remove/ replace all methods below by BlazyFilterUtil post Blazy 2.5+.
   */
  private static function unwrap($string, $container = 'slick', $item = 'slide') {
    $closing = ["/\[\/$container\]/smi"];
    $pattern = "/\[$container(.*?)\]/";

    if (mb_strpos($string, "$container]</p>") !== FALSE) {
      $closing = ["/<p\>\[\/$container\]<\/p>/smi"];
      $pattern = "/<p>\[$container(.*?)\]<\/p>/";
    }

    if (mb_strpos($string, "[$item") !== FALSE) {
      $items = ["/\[\/$item\]/smi", "/\[$item(.*?)\]/"];
      $replace = ["</$item>", "<$item$1>"];

      if (mb_strpos($string, "$item]</p>") !== FALSE) {
        $items = ["/<p\>\[\/$item\]<\/p>/smi", "/<p\>\[$item(.*?)\]<\/p>/"];
      }

      $string = preg_replace($items, $replace, $string);
    }

    preg_match_all($pattern, $string, $matches);

    // Temporarily converts to HTML tags for easy DOMXPath queries.
    if ($matches) {
      foreach ($matches[0] as $match) {
        $value = strip_tags($match);
        $value = str_replace("[", "<", $value);
        $value = str_replace("]", ">", $value);
        $string = str_replace($match, $value, $string);
      }
    }

    return preg_replace($closing, ["</$container>"], $string);
  }

  /**
   * Returns the inner HTMLof the DOMElement node.
   *
   * See http://www.php.net/manual/en/class.domelement.php#101243
   */
  private static function getHtml($node) {
    $text = '';
    foreach ($node->childNodes as $child) {
      if ($child instanceof \DOMElement) {
        $text .= $child->ownerDocument->saveXML($child);
      }
    }
    return $text;
  }

  /**
   * Returns settings for attachments.
   */
  private static function attach(array $settings = []) {
    $all = ['blazy' => TRUE, 'filter' => TRUE, 'ratio' => TRUE];
    $all['media_switch'] = $switch = $settings['media_switch'];

    if (!empty($settings[$switch])) {
      $all[$switch] = $settings[$switch];
    }

    return $all;
  }

  /**
   * Returns valid nodes based on the allowed tags.
   */
  private static function getValidNodes(\DOMDocument $dom, array $allowed_tags = [], $exclude = '') {
    $valid_nodes = [];
    foreach ($allowed_tags as $allowed_tag) {
      $nodes = $dom->getElementsByTagName($allowed_tag);
      if ($nodes->length > 0) {
        foreach ($nodes as $node) {
          if ($exclude && $node->hasAttribute($exclude)) {
            continue;
          }

          $valid_nodes[] = $node;
        }
      }
    }
    return $valid_nodes;
  }

  /**
   * Returns attributes extracted from a DOMElement if any.
   */
  private static function getAttribute($node, array $excludes = []) {
    $attributes = [];
    if ($node && $node->attributes->length) {
      foreach ($node->attributes as $attribute) {
        $name = $attribute->nodeName;
        $value = $attribute->nodeValue;
        if ($excludes && in_array($name, $excludes)) {
          continue;
        }
        $attributes[$name] = ($name == 'class') ? [$value] : $value;
      }
    }
    return $attributes ? BlazyUtil::sanitize($attributes) : [];
  }

}
