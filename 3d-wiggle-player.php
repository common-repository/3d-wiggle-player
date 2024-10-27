<?php
/*
 * Plugin Name: 3D Wiggle Player
 * Plugin URI: http://www.3dwiggle.com/plugin/wordpress
 * Description: Formats all content images on a page / post giving them 3D wiggling capabilities
 * Version: 1.1.0
 * License: GPLv2 or later
 * Author: Perspectives Software Solutions GmbH, Zurich
 * Author URI: http://www.perspectives.ch
 * Text Domain: pss-wiggle-player
 * Domain Path: /languages/
 */

/*  Copyright 2015  Perspectives Software Solutions GmbH, Zurich
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
define('WIGGLEPLAYER_TEXTDOMAIN', 'pss-wiggle-player');

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

require_once __DIR__ . '/lib/AnimationGenerator.php';
require_once __DIR__ . '/lib/AnimationParams.php';
require_once __DIR__ . '/lib/AnimationParamsReader.php';
require_once __DIR__ . '/lib/JPEGMarkersHelper.php';

add_action('admin_head', 'psswiggleplayer_add_tinymce');
add_action('wp_ajax_psswiggleplayer_read_tags', 'psswiggleplayer_ajaxreadtags');

wp_register_script('psswiggleplayer', plugins_url('js/wiggleplayer.js', __FILE__), array('jquery'), '1.0.0', true);
add_filter('the_content', 'psswiggleplayer_the_content', 0);
add_action('admin_print_footer_scripts', 'psswiggleplayer_adminfooterscripts');

wp_register_style('psswiggleplayer', plugins_url('js/wiggleplayer.css', __FILE__));

add_filter('img_caption_shortcode', 'psswiggleplayer_img_caption_shortcode_add_class', 10, 3);

function psswiggleplayer_img_caption_shortcode_add_class($output, $attr, $content)
{
    $figureId = AnimationGenerator::$figureCounter++;
    $attr['class'] = 'psswiggleplayer_' . $figureId;
    remove_filter('img_caption_shortcode', 'psswiggleplayer_img_caption_shortcode_add_class');
    $result = img_caption_shortcode($attr, $content);
    if (strpos($content, 'data-psswiggleplayer-duration') !== false) {
        $result = str_replace('data-psswiggleplayer-duration="', 'data-psswiggleplayer-figureid="' . $figureId . '" data-psswiggleplayer-duration="', $result);
    }
    add_filter('img_caption_shortcode', 'psswiggleplayer_img_caption_shortcode_add_class', 10, 3);
    return $result;
}


function psswiggleplayer_add_tinymce()
{
    global $typenow;

    $mayEditPost = current_user_can('edit_posts') && $typenow == 'post';
    $mayEditPage = current_user_can('edit_pages') && $typenow == 'page';

    if (($mayEditPage || $mayEditPost) && get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', 'psswiggleplayer_add_tinymce_plugin');
        add_filter('mce_buttons', 'psswiggleplayer_add_tinymce_button');
    }
}

function psswiggleplayer_add_tinymce_plugin($plugin_array)
{
    $plugin_array['psswiggleplayer_plugin'] = plugins_url('js/tinymce-plugin.js', __FILE__);
    return $plugin_array;
}

function psswiggleplayer_add_tinymce_button($buttons)
{
    array_push($buttons, 'psswiggleplayer_button');
    return $buttons;
}

function psswiggleplayer_ajaxreadtags()
{
    $result = new StdClass();
    if (check_ajax_referer('Z6J5jWnsAP87Av2of7Zy3sOkae3s45', 'security', false)) {
        $params = AnimationParamsReader::read(get_attached_file($_POST['id']));
        if ($params) {
            $result->status = 1;
            $result->params = $params;
            wp_send_json($result);
        }
    }
    $result->status = 2;
    wp_send_json($result);
}

function psswiggleplayer_the_content($content)
{
    global $post;
    $generator = new AnimationGenerator();
    return $generator->generate($post, $content);
}


function psswiggleplayer_adminfooterscripts()
{
    printf('<script type="text/javascript">psswiggleplayer = { nonce: "%s" };</script>', wp_create_nonce('Z6J5jWnsAP87Av2of7Zy3sOkae3s45'));
}
