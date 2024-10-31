<?php
/**
 * Plugin Name: OxyPlug - Preload
 * Plugin URI: https://www.oxyplug.com/products/oxy-preload/
 * Description: Preload post/page featured images and product images to improve the Largest Contentful Paint (LCP) and to get a better Core Web Vital (CWV) score on Google's Lighthouse.
 * Version: 1.0.0
 * Author: OxyPlug
 * Author URI: https://www.oxyplug.com
 * Requires at least: 4.9
 * Tested up to: 6.7
 *
 * Copyright 2024 OxyPlug
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Class OxyPreload
 */
class OxyPreload
{
  protected $imgurl;
  protected $srcset;
  protected $sizes;

  public function __construct()
  {
    // Add preload tag
    add_action('wp_head', array($this, 'add_preload_tag'));
  }

  public function add_preload_tag()
  {
    if (is_single() || is_page()) {
      $thumbnail_id = (int)(get_post_thumbnail_id());
      if ($thumbnail_id > 0) {
        $this->imgurl = get_the_post_thumbnail_url();
      } else if (function_exists('wc_get_product')) {
        if ($product = wc_get_product(get_the_id())) {
          $attachment_ids = $product->get_gallery_image_ids();
          if (sizeof($attachment_ids) > 0) {
            $thumbnail_id = reset($attachment_ids);
            $this->imgurl = wp_get_attachment_url($thumbnail_id);
          }
        }
      }

      if ($thumbnail_id) {
        $this->srcset = wp_get_attachment_image_srcset($thumbnail_id);
        $this->sizes = wp_get_attachment_image_sizes($thumbnail_id, 'full');
        ?>
        <link rel="preload"
              as="image"
              href="<?php esc_attr_e($this->imgurl) ?>"
              imagesrcset="<?php esc_attr_e($this->srcset) ?>"
              imagesizes="<?php esc_attr_e($this->sizes) ?>"
              fetchpriority="high">
        <?php
      }
    }
  }
}

new OxyPreload();
