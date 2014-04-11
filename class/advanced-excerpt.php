<?php
class Advanced_Excerpt {
	public $options;

	/*
	 * Some of the following options below are linked to checkboxes on the plugin's option page.
	 * If any checkbox options are added/removed/modified in the future please ensure you also update
	 * the $checkbox_options variable in the update_options() method.
	 */ 
	public $default_options = array(
		'length' => 40,
		'length_type' => 'words',
		'no_custom' => 1,
		'no_shortcode' => 1,
		'finish' => 'none',
		'ellipsis' => '&hellip;',
		'read_more' => 'Read the rest',
		'add_link' => 0,
		'allowed_tags' => array( '_all' )
	);

	public $options_basic_tags; // Basic HTML tags (determines which tags are in the checklist by default)
	public $options_all_tags; // Almost all HTML tags (extra options)

	function __construct( $plugin_file_path ) {
		$this->load_options();

		$this->plugin_version = $GLOBALS['advanced_excerpt_version'];
		$this->plugin_file_path = $plugin_file_path;
		$this->plugin_dir_path = plugin_dir_path( $plugin_file_path );
		$this->plugin_folder_name = basename( $this->plugin_dir_path );
		$this->plugin_basename = plugin_basename( $plugin_file_path );

		$this->options_basic_tags = apply_filters( 'advanced_excerpt_basic_tags', array(
			'a', 'abbr', 'acronym', 'b', 'big',
			'blockquote', 'br', 'center', 'cite', 'code', 'dd', 'del', 'div', 'dl', 'dt',
			'em', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins',
			'li', 'ol', 'p', 'pre', 'q', 's', 'small', 'span', 'strike', 'strong', 'sub',
			'sup', 'table', 'td', 'th', 'tr', 'u', 'ul'
		) );

		$this->options_all_tags = apply_filters( 'advanced_excerpt_all_tags', array(
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
		) );

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
		$update_options = false;
		if ( false !== get_option( 'advancedexcerpt_length' ) ) {
			$legacy_options = array( 'length', 'use_words', 'no_custom', 'no_shortcode', 'finish_word', 'finish_sentence', 'ellipsis', 'read_more', 'add_link', 'allowed_tags' );

			foreach ( $legacy_options as $legacy_option ) {
				$option_name = 'advancedexcerpt_' . $legacy_option;
				$this->options[$legacy_option] = get_option( $option_name );
				delete_option( $option_name );
			}
			$update_options = true;
		} else {
			$this->options = get_option( 'advanced_excerpt' );
		}

		// convert legacy option use_words to it's udpated equivalent
		if ( isset( $this->options['use_words'] ) ) {
			$this->options['length_type'] = ( 1 == $this->options['use_words'] ) ? 'words' : 'characters';
			unset( $this->options['use_words'] );
			$update_options = true;
		}

		// convert legacy options finish_word & finish_sentence to their udpated equivalents
		if ( isset( $this->options['finish_sentence'] ) ) {
			if ( 0 == $this->options['finish_word'] && 0 == $this->options['finish_sentence'] ) {
				$this->options['finish'] = 'none';
			} else if ( 1 == $this->options['finish_word'] && 1 == $this->options['finish_sentence'] ) {
				$this->options['finish'] = 'sentence';
			} else if ( 0 == $this->options['finish_word'] && 1 == $this->options['finish_sentence'] ) {
				$this->options['finish'] = 'sentence';
			} else {
				$this->options['finish'] = 'word';
			}
			unset( $this->options['finish_word'] );
			unset( $this->options['finish_sentence'] );
			$update_options = true;
		}

		// if no options exist then this is a fresh install, set up some default options
		if ( empty( $this->options ) ) {
			$this->options = $this->default_options;
			$update_options = true;
		}

		if ( $update_options ) {
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
		/*
		 * Allow developers to skip running the advanced excerpt filters on certain page types.
		 * They can do so by passing in an array of page types they'd like to skip
		 * e.g. array( 'search', 'author' );
		 */
		$page_types = $this->get_current_page_types();
		$skip_page_types = apply_filters( 'advanced_excerpt_skip_page_types', array() );
		$page_type_matches = array_intersect( $page_types, $skip_page_types );
		if ( !empty( $page_types ) && !empty( $page_type_matches ) ) return $text;

		// Extract options (skip collisions)
		if ( is_array( $this->options ) ) {
			extract( $this->options, EXTR_SKIP );
			$this->options = null; // Reset
		}
		extract( $this->default_options, EXTR_SKIP );

		// Avoid custom excerpts
		if ( !empty( $text ) && !$no_custom ) {
			return $text;
		}

		// Get the full content and filter it
		$text = get_the_content( '' );
		if ( 1 == $no_shortcode ) {
			$text = strip_shortcodes( $text );
		}
		$text = apply_filters( 'the_content', $text );

		// From the default wp_trim_excerpt():
		// Some kind of precaution against malformed CDATA in RSS feeds I suppose
		$text = str_replace( ']]>', ']]&gt;', $text );

		// Determine allowed tags
		if ( !isset( $allowed_tags ) ) {
			$allowed_tags = $this->options_all_tags;
		}

		if ( isset( $exclude_tags ) ) {
			$allowed_tags = array_diff( $allowed_tags, $exclude_tags );
		}

		// Strip HTML if allow-all is not set
		if ( !in_array( '_all', $allowed_tags ) ) {
			if ( count( $allowed_tags ) > 0 ) {
				$tag_string = '<' . implode( '><', $allowed_tags ) . '>';
			} else {
				$tag_string = '';
			}
			$text = strip_tags( $text, $tag_string );
		}

		// Create the excerpt
		$text = $this->text_excerpt( $text, $length, $length_type, $finish );

		// Add the ellipsis or link
		$text = $this->text_add_more( $text, $ellipsis, ( $add_link ) ? $read_more : false );

		return $text;
	}

	function text_excerpt( $text, $length, $length_type, $finish ) {
		$tokens = array();
		$out = '';
		$w = 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace
		// (<[^>]+>|[^<>\s]+\s*)
		preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens );
		foreach ( $tokens[0] as $t ) { // Parse each token
			if ( $w >= $length && 'sentence' != $finish ) { // Limit reached
				break;
			}
			if ( $t[0] != '<' ) { // Token is not a tag
				if ( $w >= $length && 'sentence' == $finish && preg_match( '/[\?\.\!]\s*$/uS', $t ) == 1 ) { // Limit reached, continue until ? . or ! occur at the end
					$out .= trim( $t );
					break;
				}
				if ( 'words' == $length_type ) { // Count words
					$w++;
				} else { // Count/trim characters
					$chars = trim( $t ); // Remove surrounding space
					$c = strlen( $chars );
					if ( $c + $w > $length && 'sentence' != $finish ) { // Token is too long
						$c = ( 'word' == $finish ) ? $c : $length - $w; // Keep token to finish word
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
		if ( $read_more ) {
			$link_template = apply_filters( 'advanced_excerpt_read_more_link_template', ' <a href="%s" class="read-more">%s</a>', get_permalink(), $read_more );
			$ellipsis .= sprintf( $link_template, get_permalink(), $read_more );
		}

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

		$checkbox_options = array( 'no_custom', 'no_shortcode', 'add_link' );

		foreach ( $checkbox_options as $checkbox_option ) {
			$this->options[$checkbox_option] = ( isset( $_POST[$checkbox_option] ) ) ? 1 : 0;
		}

		$this->options['length_type'] = $_POST['length_type'];
		$this->options['finish'] = $_POST['finish'];
		$this->options['ellipsis'] = $_POST['ellipsis'];
		$this->options['read_more'] = $_POST['read_more'];
		$this->options['allowed_tags'] = ( isset( $_POST['allowed_tags'] ) ) ? array_unique( (array) $_POST['allowed_tags'] ) : array();

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

		$tag_list = array_unique( array_merge( $this->options_basic_tags, $allowed_tags ) );
		sort( $tag_list );
		$tag_cols = 5;

		require_once $this->plugin_dir_path . 'template/options.php';
	}

	function get_current_page_types() {
		global $wp_query;
		if ( ! isset( $wp_query ) ) return false;
		$wp_query_object_vars = get_object_vars( $wp_query );

		$page_types = array();
		foreach( $wp_query_object_vars as $key => $value ) {
			if ( false === strpos( $key, 'is_' ) ) continue;
			if ( true === $value ) {
				$page_types[] = str_replace( 'is_', '', $key );
			}
		}

		return $page_types;
	}

}