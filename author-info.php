<?php
/*
Plugin Name: Author Info
Plugin URI: http://wordpress.org/plugins/author-info/
Description: A plugin to display author information on the widget
Version: 1.0.2
Author: Gadgets Choose
Author URI: http://onmouseenter.com/how-to-easily-insert-the-publisher-information-on-the-sidebar-of-wordpress-blog/
License: GPLv2
*/

 add_filter('plugin_action_links', 'author_info_plugin_settings_link', 10, 2);
 function author_info_plugin_settings_link($links, $file) {

    if ( $file == 'author-info/author-info.php' ) {
        /* Insert the setting link */
        $links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'options-general.php?page=author-info-options' ), __( 'Settings', 'author-info' ) );
    }
    return $links;

 }

 // include style sheet only on single post
 add_action( 'wp_enqueue_scripts', 'author_style_css' );

 function author_style_css() {

  if( is_single() ) {

   $authorStyleUrl = plugins_url('/css/author-style-sheet.css', __FILE__); // Respects SSL, author-style-sheet.css is relative to the current file
   $authorStyleFile = WP_PLUGIN_DIR . '/author-info/css/author-style-sheet.css';
   if ( file_exists($authorStyleFile) ) {
      wp_register_style('authorStyleSheets', $authorStyleUrl);
      wp_enqueue_style( 'authorStyleSheets' );
   }

  }

 }

 // add options page and register options
 if(is_admin()) {
  add_action('admin_menu','author_info_menu');
  add_action('admin_init', 'author_info_css_group_register');
 }

 //register setting fields
 function author_info_css_group_register() {
  register_setting('author_info_group','title_color');
  register_setting('author_info_group','font_size');
 }

 //author info options page setup
 function author_info_menu() {
  add_options_page('Author Info Style Settings', 'Author Info', 'manage_options', 'author-info-options', 'author_info_style_settings');
 }

 function author_info_style_settings() {
 ?>

 <div class="wrap">
  <h2><?php _e('Author Settings Page','author-info') ?></h2>
  <form method="post" action="options.php">
  <?php settings_fields('author_info_group'); ?>
   <table class="form-table">
    <tr valign="top">
     <th scope="row"><?php _e('Widget Text Color Setting : ','author-info') ?></th>
     <td><input name = "title_color" type="text" value="<?php echo esc_attr( get_option( 'title_color' ) ); ?>"/>Enter any color here, for example 'white'</td>
    </tr>
    <tr valign="top">
     <th scope="row"><?php _e('Widget Font Size Setting : ','author-info') ?></th>
     <td><input name = "font_size" type="text" value="<?php echo esc_attr( get_option( 'font_size' ) ); ?>"/>Enter any font size here, but not too big, for example 16</td>
    </tr>
   </table>
   <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save','author-info') ?>"/>
   </p>
  </form>
 </div>

<?php }

 // use widgets_init action hook to register widget
 add_action( 'widgets_init', 'author_info_widget_register' );

 //register author info widget
 function author_info_widget_register() {
  register_widget( 'author_info_Widget' );
 }

 //author_info_Widget class
 class author_info_Widget extends WP_Widget {

  //widget constructor
  function author_info_Widget() {

    $widget_ops = array(
     'classname' => 'author_info_Widget_class',
     'description' => 'Display author bio on the widget'
    );

    $this->WP_Widget( 'author_info_Widget', 'Author Info', $widget_ops );

  }

  //displays the widget form in the admin dashboard
  function form($instance) {

    $defaults = array( 'title' => 'Author Info', 'info' =>'' );
    $instance = wp_parse_args( (array) $instance, $defaults );
    $title = $instance['title'];
    $info = $instance['info'];
    ?>
    <p>Title : <input class = "widefat" name = "<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/></p>
    <p>Author Bio : <textarea class = "widefat" name = "<?php echo $this->get_field_name( 'info' ); ?>" type="text"><?php echo esc_attr( $info ); ?></textarea></p>
    <?php
   }

  //process widget options to save
  function update($new_instance, $old_instance) {

    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['info'] = strip_tags( $new_instance['info'] );

    return $instance;
  }

  //displays the widget
  function widget($args, $instance) {
   if(is_single()) {
    global $post;
    extract($args);

    echo $before_widget;
    $title = apply_filters( 'widget_title', $instance['title'] );
    $info = empty( $instance['info'] ) ? ' &nbsp; ' : $instance['info'];

    if ( !empty( $title ) ) { echo $before_title .'<p class="author_info">' . $title . '</p>'.$after_title; };
    echo '<p class="author">'.get_avatar($post->post_author).$info.'</p>';
    echo $after_widget;
   }
  }

 }

 //Adding css styles to author info Widget
 add_action('wp_head', 'author_info_Widget_styles');
 function author_info_Widget_styles() {
  echo '
   <style type="text/css">
    .author_info_Widget_class p, .avatar-96, .author_info {
      text-align:justify;
      color:'.get_option('title_color').';
      font-size:'.get_option('font_size').'px;
    }'.'
   </style>
  ';
 }
?>