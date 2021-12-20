/**
 * @file
 * Provides Slick vanilla where options can be directly injected via data-slick.
 */

(function ($, Drupal, once) {

  'use strict';

  var _id = 'slickVanilla';
  // @fixme typo at 3.x, should be BEM modifier: .slick--vanilla.
  var _element = '.slick-vanilla';

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

      // Weirdo: context may be null after Colorbox close.
      context = context || document;

      // jQuery may pass its object as non-expected context identified by length.
      context = 'length' in context ? context[0] : context;
      context = context instanceof HTMLDocument ? context : document;

      // Prevents potential missing due to the newly added sitewide option.
      once(_id, _element, context).forEach(doSlickVanilla);
    },
    detach: function (context, setting, trigger) {
      if (trigger === 'unload') {
        if (once.find(_id, context).length) {
          once.remove(_id, _element, context);
        }
      }
    }
  };

})(jQuery, Drupal, once);
