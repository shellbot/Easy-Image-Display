<?php
/*
Plugin Name: Easy Image Display
Plugin URI: http://codebyshellbot.com/wordpress-plugins/easy-image-display/
Description: An easy way to display random or latest images on your site.
Version: 1.2.4
Author: Shellbot
Author URI: http://codebyshellbot.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class sb_easy_image_display {

    function __construct() {

        /* Actions ------------------------------------------------------------------ */

        add_action( 'init', array( $this, 'sb_include_widget' ), -10 );
        add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );
        add_action('admin_init', array( $this, 'dismiss_admin_notice' ) );
        add_action( 'admin_print_scripts-widgets.php', array( $this, 'sb_easy_image_widget_js' ) );
        add_action( 'admin_print_styles-widgets.php', array( $this, 'sb_easy_image_widget_css' ) );
        add_action('admin_notices', array( $this, 'show_admin_notice' ) );

        /* Filters ------------------------------------------------------------------ */
        add_filter( 'plugin_action_links', array( $this, 'add_extra_links' ), 10, 5 );

        /* Shortcodes --------------------------------------------------------------- */

        add_shortcode( 'sb_gallery', array( $this, 'custom_gallery_shortcode' ) );
        add_shortcode( 'sb_easy_image', array( $this, 'sb_image_shortcode' ) );

    }


    /* Support & donate links ----------------------------------------------- */

    function add_extra_links( $actions, $plugin_file ) {
        static $plugin;

        if (!isset($plugin))
          	$plugin = plugin_basename(__FILE__);

        if ($plugin == $plugin_file) {

        		$donate_link = array('donate' => '<a href="http://patreon.com/shellbot" target="_blank">' . __('Donate', 'shellbotics') . '</a>');
        		$support_link = array('support' => '<a href="http://wordpress.org/support/plugin/easy-image-display" target="_blank">' . __('Support', 'shellbotics') . '</a>');

            $actions = array_merge($donate_link, $actions);
            $actions = array_merge($support_link, $actions);

        }

      	return $actions;
    }

    /* Display a notice that can be dismissed */

    function show_admin_notice() {

      if ( current_user_can( 'install_plugins' ) ) {

        global $current_user ;
          $user_id = $current_user->ID;
          /* Check that the user hasn't already clicked to ignore the message */
      	if ( ! get_user_meta($user_id, 'eid_ignore_admin_notice') ) {
          echo '<div class="update-nag">';
          echo '<h2>Thank you for using Easy Image Display</h2><p>';
          printf(__('This plugin is <a href="%1$s" target="_blank">supported through Patreon</a>. If you find it useful, please consider a small donation. Thanks! | <a href="%2$s">Hide Notice</a>'), 'http://patreon.com/shellbot', '?eid_admin_notice_ignore=0');
          echo "</p></div>";
      	}

      }

    }

    function dismiss_admin_notice() {
    	global $current_user;
      $user_id = $current_user->ID;
      /* If user clicks to ignore the notice, add that to their user meta */
      if ( isset($_GET['eid_admin_notice_ignore']) && '0' == $_GET['eid_admin_notice_ignore'] ) {
        add_user_meta($user_id, 'eid_ignore_admin_notice', 'true', true);
    	}
    }


    /* JS / CSS ------------------------------------------------------------- */

    function public_scripts() {
        wp_register_script( 'colorbox', plugin_dir_url( __FILE__ ). 'js/jquery.colorbox-min.js', array( 'jquery' ), '', true );
        wp_register_style( 'colorbox-css', plugin_dir_url( __FILE__ ). 'css/colorbox.css' );

        wp_enqueue_script( 'colorbox' );
        wp_enqueue_style( 'colorbox-css' );
    }

    function public_js( $gallery_id = '' ) {
        echo '<script type="text/javascript">
                jQuery(document).ready(function() {
                  jQuery("#' . $gallery_id . ' .gallery-icon a").colorbox({
                    maxWidth: "80%",
                    maxHeight: "80%",
                  });
                });
            </script>';
    }

    function public_css( $gallery_id = '' ) {
        echo '<style type="text/css">'
            . '#' . $gallery_id . ' .gallery-caption { display: none !important; }'
            . '</style>';
    }


    /* Widget ------------------------------------------------------------------- */

    function sb_include_widget() {
        include( 'sb-easy-image-widget.php' );
    }

    function sb_image_widget( $args ) {
        //set defaults
        $defaults = extract( shortcode_atts(array(
            'num' => '9',
            'order' => 'newest',
            'size'  => 'thumbnail',
            'link' => 'file',
            'url' => '',
            'columns' => '3',
            'filter' => 'only',
            'ids' => '',
            'captions' => 'on',
        ), $args ) );

        //rebuild $args array with custom values & defaults
        $args = array(
            'num' => $num,
            'order' => $order,
            'size'  => $size,
            'link' => $link,
            'url' => $url,
            'columns' => $columns,
            'filter' => $filter,
            'ids' => $ids,
            'captions' => $captions,
        );

        return $this->sb_get_easy_image( $args, 'shortcode' );
    }


    /* Shortcode ---------------------------------------------------------------- */

    function sb_image_shortcode( $args ) {
        //set defaults
        extract( shortcode_atts(array(
            'num' => '9',
            'order' => 'newest',
            'size'  => 'thumbnail',
            'link' => 'file',
            'url' => '',
            'columns' => '3',
            'filter' => 'only',
            'ids' => '',
            'captions' => 'on',
        ), $args ) );

        //rebuild $args array with custom values & defaults
        $args = array(
            'num' => $num,
            'order' => $order,
            'size'  => $size,
            'link' => $link,
            'url' => $url,
            'columns' => $columns,
            'filter' => $filter,
            'ids' => $ids,
            'captions' => $captions,
        );

        return $this->sb_get_easy_image( $args, 'shortcode' );
    }


    /* Template tag ------------------------------------------------------------- */

    function sb_image_tag( $args = '' ) {
        //set defaults
        extract( shortcode_atts(array(
            'num' => '5',
            'order' => 'newest',
            'size'  => 'thumbnail',
            'link' => 'file',
            'url' => '',
            'columns' => '5',
            'filter' => 'only',
            'ids' => '',
            'captions' => 'on',
        ), $args ) );

        //rebuild $args array with custom values & defaults
        $args = array(
            'num' => $num,
            'order' => $order,
            'size'  => $size,
            'link' => $link,
            'url' => $url,
            'columns' => $columns,
            'filter' => $filter,
            'ids' => $ids,
            'captions' => $captions,
        );

        return $this->sb_get_easy_image( $args );
    }


    /* Construct query and return array of images ------------------------------- */

    function sb_get_easy_image( $args, $src = '' ) {

        $query = array (
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
        );

        if( $args['num'] ) {
            $query['posts_per_page'] = $args['num'];
        }

        $args['order'] = strtolower( $args['order'] );
        switch( $args['order'] ) {
            case 'random':
                $query['orderby'] = 'rand';
            break;
            case 'oldest':
                $query['order'] = 'ASC';
            break;
            case 'custom':
                $query['orderby'] = 'post__in';
                $query['order'] = 'ASC';
            break;
        };

        if( $args['ids'] && strtolower( $args['filter'] ) == 'include' ) {

            $attachments = $this->include_action( $args, $query );

        } elseif( $args['ids'] ) {

            if( false != strpos( $args['ids'], '-' ) ) { //IDs include a range

                $temp_ids = explode( ',', $args['ids'] );
                $ids = array();

                foreach ($temp_ids as $k => $v) {

                    // Is there a dash?
                    $dash = strpos($v, '-');
                    if ($dash) {
                        $from = intval(substr($v, 0, $dash));
                        $to = intval(substr($v, $dash + 1));

                        for ($i = $from; $i <= $to; $i ++) {
                            $ids[] = "$i";
                        }
                    }
                    else { // No, just insert next in the array
                        $ids[] = "$v";
                    }
                }

            } else { //no ranges, straighforward explode
                $ids = explode( ',', $args['ids'] );
            }

            if( strtolower( $args['filter'] ) == 'exclude' ) {
                $query['post__not_in'] = $ids;
            } else {
                //Default "only"
                $query['post__in'] = $ids;
            }

            $attachments = get_posts( $query );
        } else {
            $attachments = get_posts( $query );
        }

        if ( $attachments ) {

            $ids = '';
            foreach ( $attachments as $attachment ) {
                $ids .= $attachment->ID . ', ';
            }

            return do_shortcode( '[sb_gallery columns="' . $args['columns'] . '" ids="' . $ids . '" size="' . strtolower( $args['size'] ) . '" link="' . strtolower( $args['link'] ) . '" url="' . strtolower( $args['url'] ) . ' captions="' . strtolower( $args['captions'] ) . '"]' );

        } else {
            echo 'No images to display.';
        }

    }


    /* Rejig query based on action parameter -------------------------------- */
    function include_action( $args, $query ) {

        $ids = explode( ',', $args['ids'] );

        if( count( $ids ) >= $args['num'] ) {
            //Equal or more IDs than total images.
            $query['post__in'] = $ids;

            $attachments = get_posts( $query );
        } else {
            //Less IDs than total images.
            $diff = $args['num'] - count( $ids );

            //Original query continues, but number changed to difference
            //Excludes specified IDs
            $query['posts_per_page'] = $diff;
            $query['post__not_in'] = $ids;

            $attachments = get_posts( $query );
            wp_reset_postdata();

            //new query retrieves specified IDs
            $include = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => count( $ids ),
                'post__in' => $ids,
            );
            $included = get_posts( $include );

            //smoosh all posts together
            $all = array_merge( $attachments, $included );

            //order by whatever they were supposed to be ordered by
            switch( strtolower( $args['order'] ) ) {
                case 'newest':
                    $sort = SORT_DESC;
                break;

                case 'oldest':
                    $sort = SORT_ASC;
                break;

                default:
                    $sort = 'random';
                break;
            }

            if( $sort == 'random' ) {
                shuffle( $all );
            } else {

                $date = array();

                foreach( $all as $key => $row ) {
                    $date[$key] = $row->post_date;
                }
                array_multisort( $date, $sort, $all );

            }

            //return
            return $all;

        }

        return $attachments;
    }


    /* The Gallery shortcode - modified for link="none" and link="lightbox" ----- */

    function custom_gallery_shortcode( $attr ) {

        global $post, $wp_locale;

        if ( isset( $attr['link'] ) && 'lightbox' == $attr['link'] ) {
            $attr['link'] = 'file';
            $lightbox = 1;
        }

        $output = gallery_shortcode($attr);

        //find gallery ID
        $pattern = '/gallery-[0-9]/';
        preg_match($pattern, $output, $matches);
        $gallery_id = $matches[0];

        // no link
        if ( isset( $attr['link'] ) && 'none' == $attr['link']  ) {
            $output = preg_replace( array( '/<a[^>]*>/', '/<\/a>/'), '', $output );
        }

        //static link
        if ( isset( $attr['link'] ) && 'url' == $attr['link'] && !empty( $attr['url'] ) ) {
            $pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
            $output = preg_replace( $pattern, $attr['url'], $output );
        }

        if( isset( $lightbox ) && 1 == $lightbox ) {
            $this->public_js( $gallery_id );
        }

        if( strtolower( $attr['captions'] ) == 'off' ) {
            $this->public_css( $gallery_id );
        }

        return $output;

    }


    /* A little bit of inline CSS & JS for the widget admin page ---------------- */

    function sb_easy_image_widget_css() {
        echo '<style type="text/css">
            div#sb-easy-image-advanced { border: 1px solid #ddd; padding: 10px;}
            #sb-easy-image-advanced-toggle { display: block; margin: 10px 0; }
            </style>';
    }

    function sb_easy_image_widget_js() {
        echo "<script type='text/javascript'>
                function sb_advanced_toggle(el){
                    jQuery(el).siblings('#sb-easy-image-advanced').toggle();
                }
            </script>";
    }

}

$sbcid = new sb_easy_image_display();

/*
 * This function is outside the class for use as a template tag.
 * Maybe there's a better solution, I don't know.
 */
function sb_easy_image( $args = '' ) {

    global $sbcid;
    echo $sbcid->sb_image_tag( $args );

}
