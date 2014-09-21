<?php
/**
 * Widget Name: Easy Image Display
 * Version: 1.2.1
 */


add_action( 'widgets_init', 'sb_easy_image_load_widgets' );


function sb_easy_image_load_widgets() {
    register_widget( 'SB_Easy_Image_Widget' );
}


class SB_Easy_Image_Widget extends WP_Widget {
 
    /* Widget setup --------------------------------------------------------- */
    
    function SB_Easy_Image_Widget() {

        $widget_ops = array( 
            'classname' => 'widget-sb-easy-image', 
            'description' => esc_html__('Arrange and display your uploaded images', 'shellbotics'),
        );
        
        $control_ops = array( 
            'width' => 150, 
            'height' => 350, 
            'id_base' => 'sb-easy-image-widget',
        );

        $this->WP_Widget( 'sb-easy-image-widget', esc_html__( 'SB Easy Image', 'shellbotics' ), $widget_ops, $control_ops );
    }

    
    /* Update --------------------------------------------------------------- */
    
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']   = strip_tags( $new_instance['title'] );
        $instance['num']     = strip_tags( $new_instance['num'] );
        $instance['order']   = $new_instance['order'];
        $instance['size']    = $new_instance['size'];
        $instance['columns'] = $new_instance['columns'];
        $instance['link']    = $new_instance['link'];
        $instance['url']     = $new_instance['url'];
        $instance['filter']    = $new_instance['filter'];
        $instance['ids']    = $new_instance['ids'];

        return $instance;
    }
    
    /* Return possible settings as array ---------------------------------------- */

    function sb_easy_image_params() { 

        $params = array(
            'include' => array(
                'Show all',
                'Include only',
                'Exclude',  
            ), 
            'yesno' => array(
                'Yes',
                'No',  
            ), 
            'order' => array(
                'Newest',
                'Oldest',
                'Random',
                'Custom',
            ), 
            'sizes' => array(
                'Thumbnail',
                'Medium',
                'Large',
                'Full',
            ), 
            'link' => array(
                'None',
                'Lightbox',
                'Attachment',
                'File',
                'URL',
            ),
            'filter' => array(
                'Only',
                'Include',
                'Exclude',
            ),
        );

        return $params;

    }


    /* Settings ------------------------------------------------------------- */
    
    function form( $instance ) {
        
        $params = $this->sb_easy_image_params();

            $defaults = array( 
                'title' => esc_html__( 'Latest Image', 'shellbotics' ), 
                'order' => 'newest',
                'size' => 'thumbnail',
                'num' => 1, 
                'link' => 'File',
                'url' => '',
                'columns' => 1,
                'filter' => 'Only',
                'ids' => '',
            );

            $instance = wp_parse_args( (array) $instance, $defaults ); 
            
            ?>

            <!-- Widget Title: Text Input -->
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'shellbotics' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
            </p>  

            <!-- Number of images to display -->
            <p>
                <label for="<?php echo $this->get_field_id( 'num' ); ?>"><?php esc_html_e( 'Number of images to display', 'shellbotics' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'num' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num' ); ?>" value="<?php echo $instance['num']; ?>" class="widefat" />
            </p>  

            <!-- Display order -->
            <p>
                <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php esc_html_e( 'Order images by:', 'shellbotics' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" >
                    <?php
                    foreach ( $params['order'] as $order ) {
                    ?>
                        <option value="<?php echo $order; ?>" <?php if ( $order == $instance['order'] ) { echo 'selected="selected"'; } ?>>
                            <?php echo $order; ?>
                        </option>
                    <?php
                    } 
                    ?>
                </select>
            </p>

            <a id="sb-easy-image-advanced-toggle" href="#" onclick="javascript:sb_advanced_toggle( jQuery(this) ); return false;">Advanced settings</a>

            <div id="sb-easy-image-advanced" style="display:none;">

                <!-- Size -->
                <p>
                    <label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php esc_html_e( 'Size to display:', 'shellbotics' ); ?></label>
                    <select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" >
                        <?php
                        foreach ( $params['sizes'] as $size ) {
                        ?>
                            <option value="<?php echo $size; ?>" <?php if ( $size == $instance['size'] ) { echo 'selected="selected"'; } ?>>
                                <?php echo $size; ?>
                            </option>
                        <?php
                        } 
                        ?>
                    </select>
                </p>

                <!-- Link -->
                <p>
                    <label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php esc_html_e('Link images to:', 'shellbotics'); ?></label>
                    <select id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" >
                        <?php
                        foreach ( $params['link'] as $link ) {
                        ?>
                            <option value="<?php echo $link; ?>" <?php if ( $link == $instance['link'] ) { echo 'selected="selected"'; } ?>>
                                <?php echo $link; ?>
                            </option>
                        <?php
                        } 
                        ?>
                    </select>
                </p>
                
            <!-- Static URL -->
            <p>
                <label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php esc_html_e( 'Static URL', 'shellbotics' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'url' ); ?>" type="text" name="<?php echo $this->get_field_name( 'url' ); ?>" value="<?php echo $instance['url']; ?>" class="widefat" />
            </p> 
                
            <!-- Number of columns to display -->
            <p>
                <label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php esc_html_e( 'Number of columns to display', 'shellbotics' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'columns' ); ?>" type="text" name="<?php echo $this->get_field_name( 'columns' ); ?>" value="<?php echo $instance['columns']; ?>" class="widefat" />
            </p>  
            
            <!-- Filter -->
            <p>
                <label for="<?php echo $this->get_field_id( 'filter' ); ?>"><?php esc_html_e('Filter:', 'shellbotics'); ?></label>
                <select id="<?php echo $this->get_field_id( 'filter' ); ?>" name="<?php echo $this->get_field_name( 'filter' ); ?>" >
                    <?php
                    foreach ( $params['filter'] as $filter ) {
                    ?>
                        <option value="<?php echo $filter; ?>" <?php if ( $filter == $instance['filter'] ) { echo 'selected="selected"'; } ?>>
                            <?php echo $filter; ?>
                        </option>
                    <?php
                    } 
                    ?>
                </select>
            </p>
 
            <!-- Image IDs -->
            <p>
                <label for="<?php echo $this->get_field_id( 'ids' ); ?>"><?php esc_html_e( 'Image IDs (separate with comma)', 'shellbotics' ); ?></label>
                <input id="<?php echo $this->get_field_id( 'ids' ); ?>" type="text" name="<?php echo $this->get_field_name( 'ids' ); ?>" value="<?php echo $instance['ids']; ?>" class="widefat" />
            </p>  

            </div>

    <?php
    }

    
    /* Display -------------------------------------------------------------- */
    
    function widget( $args, $instance ) {

        extract( $args );

        /* Our variables from the widget settings. */
        $settings = array(
            'title'   => apply_filters('widget_title', $instance['title'] ),
            'num'     => $instance['num'],
            'order'   => $instance['order'],
            'size'    => $instance['size'],
            'link'    => $instance['link'],  
            'url'     => $instance['url'],
            'columns' => $instance['columns'],
            'filter'  => $instance['filter'],
            'ids'     => $instance['ids'],
        );

        echo $before_widget;

        if ( $settings['title'] ) {
            echo $before_title . $settings['title'] . $after_title;
        }
        
        global $sbcid;
        echo $sbcid->sb_image_widget( $settings );

        echo $after_widget;
    }
    
} // SB_Image_Widget class ends