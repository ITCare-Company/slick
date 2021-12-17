/**
 * @file
 * Provides Slick vanilla where options can be directly injected via data-slick.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Slick utility functions.
   *
   * @param {HTMLElement} elm
   *   The slick HTML element.
   */
  function doSlickVanilla(elm) {
    $(elm).slick();
  }

  /**
   * Attaches slick behavior to HTML element identified by .slick-vanilla.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slickVanilla = {
    attach: function (context) {

      // Prevents potential missing due to the newly added sitewide option.
      var $slick = $('.slick-vanilla', context);
      if ($slick && $slick.length) {
        once('slick-vanilla', '.slick-vanilla', context).forEach(doSlickVanilla);
      }
    }
  };

})(jQuery, Drupal, once);
