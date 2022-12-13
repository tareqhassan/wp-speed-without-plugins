// Enable cache
function enable_cache() {
    // Set the time limit for the cache
    $duration = 60 * 60 * 24;
    if (!defined('WP_CACHE') || !WP_CACHE) {
        return;
    }
    $wp_cache_key = $_SERVER['REQUEST_URI'];
    if (false === ($output = get_transient($wp_cache_key))) {
        ob_start();
        set_transient($wp_cache_key, ob_get_contents(), $duration);
        ob_end_flush();
    } else {
        echo $output;
        exit;
    }
}
add_action('template_redirect', 'enable_cache');

// Minify HTML and CSS
function minify_html_css() {
    // Minify HTML
    ob_start('minify_html');
    function minify_html($buffer) {
        // Remove comments
        $buffer = preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
        // Remove whitespace
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);
        // Replace multiple spaces with a single space
        $buffer = preg_replace('/\s+/', ' ', $buffer);
        return $buffer;
    }

    // Minify CSS
    ob_start('minify_css');
    function minify_css($buffer) {
        // Remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // Remove whitespace
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        return $buffer;
    }
}
add_action('template_redirect', 'minify_html_css');


function lazyload_images() {
  // Don't lazy-load if the page is being served over HTTPS or if lazy-load is disabled in the customizer.
  if ( is_ssl() || ! get_theme_mod( 'lazy_load_media' ) ) {
    return;
  }

  // Enqueue the lazy-load script.
  wp_enqueue_script( 'lazyload', get_template_directory_uri() . '/js/lazyload.js', array(), '1.0', true );

  // Add a hook to filter the image attributes and add the lazy-load class.
  add_filter( 'wp_get_attachment_image_attributes', 'lazyload_image_attributes', 10, 3 );
}
add_action( 'wp_enqueue_scripts', 'lazyload_images' );

function lazyload_image_attributes( $attr, $attachment, $size ) {
  $attr['class'] .= ' lazyload';

  return $attr;
}
