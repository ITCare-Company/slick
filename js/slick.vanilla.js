/**
 * @file
 * Provides Slick vanilla where options can be directly injected via data-slick.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Slick utility functions.
   *
   * @param {int} i
   *   The index of the current element.
   * @param {HTMLElement} elm
   *   The slick HTML element.
   */
  function doSlickVanilla(i, elm) {
    $(elm).slick();
  }

  /**
   * Attaches slick behavior to HTML element identified by .slick-vanilla.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.slickVanilla = {
    attach: function (context) {
      $('.slick-vanilla', context).once('slick-vanilla').each(doSlickVanilla);
    }
  };

})(jQuery, Drupal);
