<?php

namespace Drupal\slick;

use Drupal\Component\Plugin\Mapper\MapperInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements SlickSkinManagerInterface.
 */
class SlickSkinManager extends DefaultPluginManager implements SlickSkinManagerInterface, MapperInterface {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Static cache for the skin definition.
   *
   * @var array
   */
  protected $skinDefinition;

  /**
   * Static cache for the skins by group.
   *
   * @var array
   */
  protected $skinsByGroup;

  /**
   * The library info definition.
   *
   * @var array
   */
  protected $libraryInfoBuild;

  /**
   * The easing libray path.
   *
   * @var string|bool
   */
  protected $easingPath;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config) {
    parent::__construct('Plugin/slick', $namespaces, $module_handler, SlickSkinPluginInterface::class, 'Drupal\slick\Annotation\SlickSkin');

    $this->config = $config;
    $this->alterInfo('slick_skin_info');
    $this->setCacheBackend($cache_backend, 'slick_skin_plugins');
  }

  /**
   * Returns the supported skins.
   */
  public function getConstantSkins() {
    return [
      'browser',
      'lightbox',
      'overlay',
      'main',
      'thumbnail',
      'arrows',
      'dots',
      'widget',
    ];
  }

  /**
   * Returns slick config shortcut.
   */
  public function config() {
    return $this->config->get('slick.settings');
  }

  /**
   * Returns cache backend service.
   */
  public function getCache() {
    return $this->cacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function load($plugin_id) {
    return $this->createInstance($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple() {
    $skins = [];
    foreach ($this->getDefinitions() as $definition) {
      array_push($skins, $this->createInstance($definition['id']));
    }
    return $skins;
  }

  /**
   * Returns slick skins registered via SlickSkin plugin and or defaults.
   */
  public function getSkins() {
    if (!isset($this->skinDefinition)) {
      $cid = 'slick_skins_data';

      if ($cache = $this->cacheBackend->get($cid)) {
        $this->skinDefinition = $cache->data;
      }
      else {
        $methods = ['skins', 'arrows', 'dots'];
        $skins = $items = [];
        foreach ($this->loadMultiple() as $skin) {
          foreach ($methods as $method) {
            $items[$method] = $skin->{$method}();
          }
          $skins = NestedArray::mergeDeep($skins, $items);
        }

        // @todo deprecate or for the new plugin system at slick:8.x-3.0.
        $disabled = $this->config()->get('disable_old_skins');
        if (empty($disabled)) {
          $slick = \Drupal::service('slick.manager');
          if (method_exists($slick, 'buildSkins')) {
            // See \Drupal\blazy\BlazyManagerBase::buildSkins().
            if ($old_skins = $slick->buildSkins('slick', '\Drupal\slick\SlickSkin', $methods)) {
              $skins = NestedArray::mergeDeep($old_skins, $skins);
            }
          }
        }

        $count = isset($items['skins']) ? count($items['skins']) : count($items);
        $tags = Cache::buildTags($cid, ['count:' . $count]);
        $this->cacheBackend->set($cid, $skins, Cache::PERMANENT, $tags);

        $this->skinDefinition = $skins;
      }
    }
    return $this->skinDefinition;
  }

  /**
   * Returns available slick skins by group.
   */
  public function getSkinsByGroup($group = '', $option = FALSE) {
    if (!isset($this->skinsByGroup[$group])) {
      $skins         = $groups = $ungroups = [];
      $nav_skins     = in_array($group, ['arrows', 'dots']);
      $defined_skins = $nav_skins ? $this->getSkins()[$group] : $this->getSkins()['skins'];

      foreach ($defined_skins as $skin => $properties) {
        $item = $option ? strip_tags($properties['name']) : $properties;
        if (!empty($group)) {
          if (isset($properties['group'])) {
            if ($properties['group'] != $group) {
              continue;
            }
            $groups[$skin] = $item;
          }
          elseif (!$nav_skins) {
            $ungroups[$skin] = $item;
          }
        }
        $skins[$skin] = $item;
      }
      $this->skinsByGroup[$group] = $group ? array_merge($ungroups, $groups) : $skins;
    }
    return $this->skinsByGroup[$group];
  }

  /**
   * Implements hook_library_info_build().
   */
  public function libraryInfoBuild() {
    if (!isset($this->libraryInfoBuild)) {
      $libraries['slick.css'] = [
        'dependencies' => ['slick/slick'],
        'css' => [
          'theme' => ['/libraries/slick/slick/slick-theme.css' => ['weight' => -2]],
        ],
      ];

      foreach ($this->getConstantSkins() as $group) {
        if ($skins = $this->getSkinsByGroup($group)) {
          foreach ($skins as $key => $skin) {
            $provider = isset($skin['provider']) ? $skin['provider'] : 'slick';
            $id = $provider . '.' . $group . '.' . $key;

            foreach (['css', 'js', 'dependencies'] as $property) {
              if (isset($skin[$property]) && is_array($skin[$property])) {
                $libraries[$id][$property] = $skin[$property];
              }
            }
          }
        }
      }

      $this->libraryInfoBuild = $libraries;
    }
    return $this->libraryInfoBuild;
  }

  /**
   * Provides skins only if required.
   */
  public function attachSkin(array &$load, $attach = []) {
    if ($this->config()->get('slick_css')) {
      $load['library'][] = 'slick/slick.css';
    }

    if ($this->config()->get('module_css', 'slick.settings')) {
      $load['library'][] = 'slick/slick.theme';
    }

    if (!empty($attach['thumbnail_effect'])) {
      $load['library'][] = 'slick/slick.thumbnail.' . $attach['thumbnail_effect'];
    }

    if (!empty($attach['down_arrow'])) {
      $load['library'][] = 'slick/slick.arrow.down';
    }

    foreach ($this->getConstantSkins() as $group) {
      $skin = $group == 'main' ? $attach['skin'] : (isset($attach['skin_' . $group]) ? $attach['skin_' . $group] : '');
      if (!empty($skin)) {
        $skins = $this->getSkinsByGroup($group);
        $provider = isset($skins[$skin]['provider']) ? $skins[$skin]['provider'] : 'slick';
        $load['library'][] = 'slick/' . $provider . '.' . $group . '.' . $skin;
      }
    }
  }

  /**
   * Returns easing library path if available, else FALSE.
   */
  public function getEasingPath() {
    if (!isset($this->easingPath)) {
      if (slick_libraries_get_path('easing') || slick_libraries_get_path('jquery.easing')) {
        $library_easing = slick_libraries_get_path('easing') ?: slick_libraries_get_path('jquery.easing');
        if ($library_easing) {
          $easing_path = $library_easing . '/jquery.easing.min.js';
          // Composer via bower-asset puts the library within `js` directory.
          if (!is_file($easing_path)) {
            $easing_path = $library_easing . '/js/jquery.easing.min.js';
          }
        }
      }
      else {
        $easing_path = DRUPAL_ROOT . '/libraries/easing/jquery.easing.min.js';
      }
      $this->easingPath = isset($easing_path) && is_file($easing_path) ? $easing_path : FALSE;
    }
    return $this->easingPath;
  }

  /**
   * Implements hook_library_info_alter().
   */
  public function libraryInfoAlter(&$libraries, $extension) {
    $library_path = slick_libraries_get_path('slick') ?: slick_libraries_get_path('slick-carousel');
    if ($library_path) {
      $libraries['slick']['js'] = ['/' . $library_path . '/slick/slick.min.js' => ['weight' => -3]];
      $libraries['slick']['css']['base'] = ['/' . $library_path . '/slick/slick.css' => []];
      $libraries['slick.css']['css']['theme'] = ['/' . $library_path . '/slick/slick-theme.css' => ['weight' => -2]];
    }

    if ($easing_path = $this->getEasingPath()) {
      $libraries['slick.easing']['js'] = ['/' . $easing_path => ['weight' => -4]];
    }

    $library_mousewheel = slick_libraries_get_path('mousewheel') ?: slick_libraries_get_path('jquery-mousewheel');
    if ($library_mousewheel) {
      $libraries['slick.mousewheel']['js'] = ['/' . $library_mousewheel . '/jquery.mousewheel.min.js' => ['weight' => -4]];
    }
  }

}
