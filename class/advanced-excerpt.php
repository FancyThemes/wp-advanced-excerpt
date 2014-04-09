<?php
class Advanced_Excerpt {
	public $options;

	public $default_options = array(
		'length' => 40,
		'use_words' => 1,
		'no_custom' => 1,
		'no_shortcode' => 1,
		'finish_word' => 0,
		'finish_sentence' => 0,
		'ellipsis' => '&hellip;',
		'read_more' => 'Read the rest',
		'add_link' => 0,
		'allowed_tags' => array( '_all' )
	);

	public $checkbox_options = array( 'use_words', 'no_custom', 'no_shortcode', 'finish_word', 'finish_sentence', 'add_link' );

	// Basic HTML tags (determines which tags are in the checklist by default)
	public static $options_basic_tags = array(
		'a', 'abbr', 'acronym', 'b', 'big',
		'blockquote', 'br', 'center', 'cite', 'code', 'dd', 'del', 'div', 'dl', 'dt',
		'em', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins',
		'li', 'ol', 'p', 'pre', 'q', 's', 'small', 'span', 'strike', 'strong', 'sub',
		'sup', 'table', 'td', 'th', 'tr', 'u', 'ul'
	);

	// Almost all HTML tags (extra options)
	public static $options_all_tags = array(
		'a', 'abbr', 'acronym', 'address', 'applet',
		'area', 'b', 'bdo', 'big', 'blockquote', 'br', 'button', 'caption', 'center',
		'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl',
		'dt', 'em', 'fieldset', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3',
		'h4', 'h5', 'h6', 'hr', 'i', 'iframe', 'img', 'input', 'ins', 'isindex', 'kbd',
		'label', 'legend', 'li', 'map', 'menu', 'noframes', 'noscript', 'object',
		'ol', 'optgroup', 'option', 'p', 'param', 'pre', 'q', 's', 'samp', 'script',
		'select', 'small', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table',
		'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul',
		'var'
	);

	function __construct( $plugin_file_path ) {
		$this->load_options();

		$this->plugin_version = $GLOBALS['advanced_excerpt_version'];
		$this->plugin_file_path = $plugin_file_path;
		$this->plugin_dir_path = plugin_dir_path( $plugin_file_path );
		$this->plugin_folder_name = basename( $this->plugin_dir_path );
		$this->plugin_basename = plugin_basename( $plugin_file_path );
		$this->template_dir = $this->plugin_dir_path . 'template/';

		if ( is_admin() ) {
			$this->admin_init();
		}

		remove_all_filters( 'get_the_excerpt' );
		add_filter( 'get_the_excerpt', array( $this, 'filter' ) );
	}

	function admin_init() {
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
	}

	function load_options() {
		/* 
		 * An older version of this plugin used to individually store each of it's options as a row in wp_options (1 row per option).
		 * The code below checks if their installations once used an older version of this plugin and attempts to update
		 * the option storage to the new method (all options stored in a single row in the DB as an array)
		*/
		if ( false !== get_option( 'advancedexcerpt_length' ) ) {
			$legacy_options = array( 'length', 'use_words', 'no_custom', 'no_shortcode', 'finish_word', 'finish_sentence', 'ellipsis', 'read_more', 'add_link', 'allowed_tags' );
		
			$options = array();
			foreach ( $legacy_options as $legacy_option ) {
				$option_name = 'advancedexcerpt_' . $legacy_option;
				$settings[$legacy_option] = get_option( $option_name );
				delete_option( $option_name );
			}

			update_option( 'advanced_excerpt', $options );
			$this->options = $options;
		} else {
			$this->options = get_option( 'advanced_excerpt' );
		}

		// if no options exist then this is a fresh install, set up some default options
		if ( empty( $this->options ) ) {
			$this->options = $this->default_options;
			update_option( 'advanced_excerpt', $this->options );
		}
	}

	function add_pages() {
		$options_page = add_options_page( __( "Advanced Excerpt Options", 'advanced-excerpt' ), __( "Excerpt", 'advanced-excerpt' ), 'manage_options', 'advanced-excerpt', array( $this, 'page_options' ) );
		// Scripts
		add_action( 'admin_print_scripts-' . $options_page, array( $this, 'page_script' ) );
	}

	function page_script() {
		$version = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : $this->plugin_version;
		$plugins_url = trailingslashit( plugins_url() ) . trailingslashit( $this->plugin_folder_name );
		$src = $plugins_url . 'assets/js/advanced-excerpt.js';
		wp_enqueue_script( 'advanced-excerpt-script', $src, array( 'jquery' ), $version, true );
	}

	function filter( $text ) {
		// Extract options (skip collisions)
		if ( is_array( $this->options ) ) {
			extract( $this->options, EXTR_SKIP );
			$this->options = null; // Reset
		}
		extract( $this->default_options, EXTR_SKIP );

		// Avoid custom excerpts
		if ( !empty( $text ) && !$no_custom )
			return $text;

		// Get the full content and filter it
		$text = get_the_content( '' );
		if ( 1 == $no_shortcode )
			$text = strip_shortcodes( $text );
		$text = apply_filters( 'the_content', $text );

		// From the default wp_trim_excerpt():
		// Some kind of precaution against malformed CDATA in RSS feeds I suppose
		$text = str_replace( ']]>', ']]&gt;', $text );

		// Determine allowed tags
		if ( !isset( $allowed_tags ) )
			$allowed_tags = self::$options_all_tags;

		if ( isset( $exclude_tags ) )
			$allowed_tags = array_diff( $allowed_tags, $exclude_tags );

		// Strip HTML if allow-all is not set
		if ( !in_array( '_all', $allowed_tags ) ) {
			if ( count( $allowed_tags ) > 0 )
				$tag_string = '<' . implode( '><', $allowed_tags ) . '>';
			else
				$tag_string = '';
			$text = strip_tags( $text, $tag_string );
		}

		// Create the excerpt
		$text = $this->text_excerpt( $text, $length, $use_words, $finish_word, $finish_sentence );

		// Add the ellipsis or link
		$text = $this->text_add_more( $text, $ellipsis, ( $add_link ) ? $read_more : false );

		return $text;
	}

	function text_excerpt( $text, $length, $use_words, $finish_word, $finish_sentence ) {
		$tokens = array();
		$out = '';
		$w = 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace
		// (<[^>]+>|[^<>\s]+\s*)
		preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens );
		foreach ( $tokens[0] as $t ) { // Parse each token
			if ( $w >= $length && !$finish_sentence ) { // Limit reached
				break;
			}
			if ( $t[0] != '<' ) { // Token is not a tag
				if ( $w >= $length && $finish_sentence && preg_match( '/[\?\.\!]\s*$/uS', $t ) == 1 ) { // Limit reached, continue until ? . or ! occur at the end
					$out .= trim( $t );
					break;
				}
				if ( 1 == $use_words ) { // Count words
					$w++;
				} else { // Count/trim characters
					$chars = trim( $t ); // Remove surrounding space
					$c = strlen( $chars );
					if ( $c + $w > $length && !$finish_sentence ) { // Token is too long
						$c = ( $finish_word ) ? $c : $length - $w; // Keep token to finish word
						$t = substr( $t, 0, $c );
					}
					$w += $c;
				}
			}
			// Append what's left of the token
			$out .= $t;
		}

		return trim( force_balance_tags( $out ) );
	}

	public function text_add_more( $text, $ellipsis, $read_more ) {
		if ( $read_more )
			$ellipsis .= sprintf( ' <a href="%s" class="read_more">%s</a>', get_permalink(), $read_more );

		$pos = strrpos( $text, '</' );
		if ( $pos !== false ) {
			// Inside last HTML tag
			$text = substr_replace( $text, $ellipsis, $pos, 0 );
		} else {
			// After the content
			$text .= $ellipsis;
		}

		return $text;
	}

	function update_options() {
		$_POST = stripslashes_deep( $_POST );
		$this->options['length'] = (int) $_POST['length'];

		foreach ( $this->checkbox_options as $checkbox_option ) {
			$this->options[$checkbox_option] = ( isset( $_POST[$checkbox_option] ) ) ? 1 : 0;
		}

		$this->options['ellipsis'] = $_POST['ellipsis'];
		$this->options['read_more'] = $_POST['read_more'];
		$this->options['allowed_tags'] = array_unique( (array) $_POST['allowed_tags'] );

		update_option( 'advanced_excerpt', $this->options );

		echo '<div id="message" class="updated fade"><p>' . __( 'Options saved.', 'advanced-excerpt' ) . '</p></div>';
	}

	function page_options() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'advanced_excerpt_update_options' );
			$this->update_options();
		}

		extract( $this->options, EXTR_SKIP );

		$ellipsis	= htmlentities( $ellipsis );
		$read_more	= htmlentities( $read_more );

		$tag_list = array_unique( self::$options_basic_tags + $allowed_tags );
		sort( $tag_list );
		$tag_cols = 5;

		require_once $this->plugin_dir_path . 'template/options.php';
	}

}