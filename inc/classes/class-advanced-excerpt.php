<?php
/**
 * The main Advanced Excerpt plugin class.
 *
 * @package Advanced_Excerpt
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The main Advanced Excerpt plugin class.
 */
final class Advanced_Excerpt {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Some of the following options below are linked to checkboxes on the plugin's option page.
	 * If any checkbox options are added/removed/modified in the future please ensure you also update
	 * the $checkbox_options variable in the update_options() method.
	 *
	 * @var array
	 */
	public $default_options = array(
		'length'               => 40,
		'length_type'          => 'words',
		'no_custom'            => 1,
		'no_shortcode'         => 1,
		'finish'               => 'exact',
		'ellipsis'             => '&hellip;',
		'read_more'            => 'Read the rest',
		'add_link'             => 0,
		'allowed_tags'         => array(),
		'the_excerpt'          => 1,
		'the_content'          => 1,
		'the_content_no_break' => 0,
		'exclude_pages'        => array(),
		'allowed_tags_option'  => 'dont_remove_any',
	);

	/**
	 * Basic HTML tags (determines which tags are in the checklist by default)/
	 * @var array
	 */
	public $options_basic_tags;

	/**
	 * Almost all HTML tags (extra options).
	 * @var array
	 */
	public $options_all_tags;

	/**
	 * Determines wether we're filtering the_content or the_excerpt at any given time.
	 * @var array
	 */
	public $filter_type;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Stores a copy of the loader instance in a local property for ease of access.
		$this->loader = advanced_excerpt();

		$this->load_options();

		$this->options_basic_tags = apply_filters( 'advanced_excerpt_basic_tags',
			array(
				'a',
				'abbr',
				'acronym',
				'address',
				'article',
				'aside',
				'audio',
				'b',
				'big',
				'blockquote',
				'br',
				'canvas',
				'center',
				'cite',
				'code',
				'dd',
				'del',
				'div',
				'dl',
				'dt',
				'em',
				'embed',
				'form',
				'footer',
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'header',
				'hr',
				'i',
				'img',
				'ins',
				'li',
				'nav',
				'ol',
				'p',
				'pre',
				'q',
				's',
				'section',
				'small',
				'span',
				'strike',
				'strong',
				'sub',
				'sup',
				'svg',
				'table',
				'td',
				'template',
				'th',
				'time',
				'tr',
				'u',
				'ul',
				'video',
			)
		);

		$this->options_all_tags = apply_filters( 'advanced_excerpt_all_tags',
			array(
				'a',
				'abbr',
				'acronym',
				'address',
				'applet',
				'area',
				'article',
				'aside',
				'audio',
				'b',
				'bdi',
				'bdo',
				'big',
				'blockquote',
				'br',
				'button',
				'canvas',
				'caption',
				'center',
				'cite',
				'code',
				'col',
				'colgroup',
				'data',
				'datalist',
				'dd',
				'del',
				'details',
				'dfn',
				'dialog',
				'dir',
				'div',
				'dl',
				'dt',
				'em',
				'embed',
				'fieldset',
				'figcaption',
				'figure',
				'font',
				'footer',
				'form',
				'frame',
				'frameset',
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'header',
				'hr',
				'i',
				'iframe',
				'img',
				'input',
				'ins',
				'isindex',
				'kbd',
				'keygen',
				'label',
				'legend',
				'li',
				'main',
				'map',
				'mark',
				'math',
				'menu',
				'menuitem',
				'meter',
				'nav',
				'noframes',
				'noscript',
				'object',
				'ol',
				'optgroup',
				'option',
				'output',
				'p',
				'param',
				'pre',
				'progress',
				'q',
				'rp',
				'rt',
				'ruby',
				's',
				'samp',
				'script',
				'section',
				'select',
				'small',
				'source',
				'span',
				'strike',
				'strong',
				'style',
				'sub',
				'summary',
				'sup',
				'svg',
				'table',
				'tbody',
				'td',
				'template',
				'textarea',
				'tfoot',
				'th',
				'thead',
				'time',
				'tr',
				'track',
				'tt',
				'u',
				'ul',
				'var',
				'video',
				'wbr',
			)
		);

		if ( is_admin() ) {
			$this->admin_init();
		}

		add_action( 'loop_start', array( $this, 'hook_content_filters' ) );
	}

	public function hook_content_filters() {

		if ( $this->maybe_skip_excerpt_filtering() ) {
			return;
		}

		if ( 1 == $this->options['the_excerpt'] ) {
			remove_all_filters( 'get_the_excerpt' );
			remove_all_filters( 'the_excerpt' );
			add_filter( 'get_the_excerpt', array( $this, 'filter_excerpt' ) );
		}

		if ( 1 == $this->options['the_content'] ) {
			add_filter( 'the_content', array( $this, 'filter_content' ) );
		}
	}

	/*
	 * Allow developers to skip running the advanced excerpt filters on certain page types.
	 * They can do so by using the "Disable On" checkboxes on the options page or
	 * by passing in an array of page types they'd like to skip
	 * e.g. array( 'search', 'author' );
	 * The filter, when implemented, takes precedence over the options page selection.
	 *
	 * WordPress default themes (and others) do not use the_excerpt() or get_the_excerpt()
	 * and instead use the_content(). As such, we also need to hook into the_content().
	 * To ensure we're not changing the content of single posts / pages we automatically exclude 'singular' page types.
	 */
	public function maybe_skip_excerpt_filtering() {

		$page_types = $this->get_current_page_types();
		$skip_page_types = array_unique( array_merge( array( 'singular' ), $this->options['exclude_pages'] ) );
		$skip_page_types = array_unique( apply_filters( 'advanced_excerpt_skip_page_types', $skip_page_types ) );
		$page_type_matches = array_intersect( $page_types, $skip_page_types );

		if ( is_front_page() && ! in_array( 'front', (array) $skip_page_types ) ) {
			return false;
		}

		if ( is_home() && ! in_array( 'home', (array) $skip_page_types ) ) {
			return false;
		}

		if ( empty( $page_types ) || ! empty( $page_type_matches ) ) {
			return true;
		}

		return false;
	}

	public function admin_init() {

		// Adds the "Excerpt" admin menu.
		add_action( 'admin_menu', array( $this, 'add_pages' ) );

		// Adds the "settings" link to the plugin row on the plugins admin page.
		add_filter( 'plugin_action_links_' . $this->loader->basename, array( $this, 'plugin_action_links' ) );

		// Process a settings update.
		add_action( 'admin_post_advanced_excerpt_update_options', array( $this, 'process_settings_update' ) );
	}

	public function process_settings_update() {

		check_admin_referer( 'advanced_excerpt_update_options' );
		$this->update_options();
	}

	public function load_options() {

		/*
		 * An older version of this plugin used to individually store each of it's options as a row in wp_options (1 row per option).
		 * The code below checks if their installations once used an older version of this plugin and attempts to update
		 * the option storage to the new method (all options stored in a single row in the DB as an array)
		 */
		$update_options = false;
		$update_from_legacy = false;

		if ( false !== get_option( 'advancedexcerpt_length' ) ) {
			$legacy_options = array( 'length', 'use_words', 'no_custom', 'no_shortcode', 'finish_word', 'finish_sentence', 'ellipsis', 'read_more', 'add_link', 'allowed_tags' );

			foreach ( $legacy_options as $legacy_option ) {
				$option_name = 'advancedexcerpt_' . $legacy_option;
				$this->options[ $legacy_option ] = get_option( $option_name );
				delete_option( $option_name );
			}

			// Filtering the_content() is disabled by default when migrating from version 4.1.1 of the plugin.
			$this->options['the_excerpt'] = 1;
			$this->options['the_content'] = 0;

			$update_options = true;
			$update_from_legacy = true;
		} else {
			$this->options = get_option( 'advanced_excerpt' );
		}

		// Convert legacy option use_words to it's udpated equivalent.
		if ( isset( $this->options['use_words'] ) ) {
			$this->options['length_type'] = ( 1 == $this->options['use_words'] ) ? 'words' : 'characters';
			unset( $this->options['use_words'] );
			$update_options = true;
		}

		// Convert legacy options finish_word & finish_sentence to their udpated equivalents.
		if ( isset( $this->options['finish_sentence'] ) ) {
			if ( 0 == $this->options['finish_word'] && 0 == $this->options['finish_sentence'] ) {
				$this->options['finish'] = 'exact';
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

		// Convert legacy option '_all' in the allowed_tags option to it's updated equivalent.
		if ( isset( $this->options['allowed_tags'] ) ) {
			if ( false !== ( $all_key = array_search( '_all', $this->options['allowed_tags'] ) ) ) {
				unset( $this->options['allowed_tags'][ $all_key ] );
				$this->options['allowed_tags_option'] = 'dont_remove_any';
			} elseif ( $update_from_legacy ) {
				$this->options['allowed_tags_option'] = 'remove_all_tags_except';
			}
		}

		// If no options exist then this is a fresh install, set up some default options.
		if ( empty( $this->options ) ) {
			$this->options = $this->default_options;
			$update_options = true;
		}

		$this->options = wp_parse_args( $this->options, $this->default_options );

		if ( $update_options ) {
			update_option( 'advanced_excerpt', $this->options );
		}
	}

	public function add_pages() {

		$options_page = add_options_page( __( 'Advanced Excerpt Options', 'advanced-excerpt' ), __( 'Excerpt', 'advanced-excerpt' ), 'manage_options', 'advanced-excerpt', array( $this, 'page_options' ) );

		// Enqueue assets.
		add_action( 'admin_print_scripts-' . $options_page, array( $this, 'page_assets' ) );
	}

	public function page_assets() {

		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $this->loader->version;
		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// CSS.
		wp_enqueue_style( 'advanced-excerpt-styles', $this->loader->css_url . 'style' . $suffix . '.css', array(), $version );

		// JS.
		wp_enqueue_script( 'advanced-excerpt-script', $this->loader->js_url . 'advanced-excerpt' . $suffix . '.js', array( 'jquery' ), $version, true );
	}

	public function plugin_action_links( $links ) {

		$link = sprintf( '<a href="%s">%s</a>', $this->get_settings_page_url(), __( 'Settings', 'advanced-excerpt' ) );
		array_unshift( $links, $link );

		return $links;
	}

	public function filter_content( $content ) {

		$this->filter_type = 'content';
		return $this->filter( $content );
	}

	public function filter_excerpt( $content ) {

		$this->filter_type = 'excerpt';
		return $this->filter( $content );
	}

	public function filter( $content ) {

		extract( wp_parse_args( $this->options, $this->default_options ), EXTR_SKIP );

		if ( true === apply_filters( 'advanced_excerpt_skip_excerpt_filtering', false ) ) {
			return $content;
		}

		global $post;
		if ( $the_content_no_break && false !== strpos( $post->post_content, '<!--more-->' ) && 'content' == $this->filter_type ) {
			return $content;
		}

		// Avoid custom excerpts.
		if ( ! empty( $content ) && ! $no_custom ) {
			return $content;
		}

		// Prevent recursion on 'the_content' hook.
		$content_has_filter = false;
		if ( has_filter( 'the_content', array( $this, 'filter_content' ) ) ) {
			remove_filter( 'the_content', array( $this, 'filter_content' ) );
			$content_has_filter = true;
		}

        $text = $content;//8iv: we should not initialize $content here :) bcoz it will lead to change in content.

		// Strip shortcodes if $no_shortcode is set to 1.
		if ( 1 === $no_shortcode ) {
			$text = strip_shortcodes( $text );
		}

		$text = apply_filters( 'the_content', $text );

		// Add our filter back in.
		if ( $content_has_filter ) {
			add_filter( 'the_content', array( $this, 'filter_content' ) );
		}

		// From the default wp_trim_excerpt().
		// Some kind of precaution against malformed CDATA in RSS feeds I suppose.
		$text = str_replace( ']]>', ']]&gt;', $text );

		if ( empty( $allowed_tags ) ) {
			$allowed_tags = array();
		}

		// The $exclude_tags args takes precedence over the $allowed_tags args (only if they're both defined).
		if ( ! empty( $exclude_tags ) ) {
			$allowed_tags = array_diff( $this->options_all_tags, $exclude_tags );
		}

		// Strip HTML if $allowed_tags_option is set to 'remove_all_tags_except'.
		if ( 'remove_all_tags_except' === $allowed_tags_option ) {
			if ( count( $allowed_tags ) > 0 ) {
				$tag_string = '<' . implode( '><', $allowed_tags ) . '>';
			} else {
				$tag_string = '';
			}

			$text = strip_tags( $text, $tag_string );
		}

		$text_before_trimming = $text;

		// Create the excerpt.
		$text = $this->text_excerpt( $text, $length, $length_type, $finish );

		// Add the ellipsis or link.
		if ( ! apply_filters( 'advanced_excerpt_disable_add_more', false, $text_before_trimming, $this->options ) ) {
			$text = $this->text_add_more( $text, $ellipsis, ( $add_link ) ? $read_more : false );
		}

		return apply_filters( 'advanced_excerpt_content', $text );
	}

	public function text_excerpt( $text, $length, $length_type, $finish ) {

		$tokens = array();
		$out = '';
		$w = 0;

		// Divide the string into tokens; HTML tags, or words, followed by any whitespace.
		// (<[^>]+>|[^<>\s]+\s*).
		preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens );
		foreach ( $tokens[0] as $t ) { // Parse each token.
			if ( $w >= $length && 'sentence' != $finish ) { // Limit reached.
				break;
			}
			if ( '<' != $t[0] ) { // Token is not a tag.
				if ( $w >= $length && 'sentence' == $finish && preg_match( '/[\?\.\!]\s*$/uS', $t ) == 1 ) { // Limit reached, continue until ? . or ! occur at the end.
					$out .= trim( $t );
					break;
				}
				if ( 'words' == $length_type ) { // Count words.
					$w++;
				} else { // Count/trim characters.
					$chars = trim( $t ); // Remove surrounding space.
					$c = strlen( $chars );
					if ( $c + $w > $length && 'sentence' != $finish ) { // Token is too long.
						$c = ( 'word' == $finish ) ? $c : $length - $w; // Keep token to finish word.
						$t = substr( $t, 0, $c );
					}
					$w += $c;
				}
			}
			// Append what's left of the token.
			$out .= $t;
		}

		return trim( force_balance_tags( $out ) );
	}

	public function text_add_more( $text, $ellipsis, $read_more ) {

		if ( $read_more ) {
			$link_template = apply_filters( 'advanced_excerpt_read_more_link_template', ' <a href="%1$s" class="read-more">%2$s</a>', get_permalink(), $read_more );
			$ellipsis .= sprintf( $link_template, get_permalink(), $read_more );
		}

		$pos = strrpos( $text, '</' );

		if ( false !== $pos ) {
			// Get the "clean" name of the last closing tag in the text, e.g. p, a, strong, div.
			$last_tag = strtolower( trim( str_replace( array( '<', '/', '>' ), '', substr( $text, $pos ) ) ) );

			/*
			 * There was previously a problem where our 'read-more' links were being appending incorrectly into unsuitable HTML tags.
			 * As such we're now maintaining a whitelist of HTML tags that are suitable for being appended into.
			 */
			$allow_tags_to_append_into = apply_filters( 'advanced_excerpt_allow_tags_to_append_into', array( 'p', 'div', 'article', 'section' ) );

			if ( ! in_array( $last_tag, $allow_tags_to_append_into ) ) {
				// After the content.
				$text .= $ellipsis;
				return $text;
			}
			// Inside last HTML tag.
			$text = substr_replace( $text, $ellipsis, $pos, 0 );
			return $text;
		}

		// After the content.
		$text .= $ellipsis;
		return $text;
	}

	public function update_options() {

		$post_data = wp_unslash( $_POST );
		$this->options['length'] = (int) $post_data['length'];

		$checkbox_options = array( 'no_custom', 'no_shortcode', 'add_link', 'the_excerpt', 'the_content', 'the_content_no_break' );

		foreach ( $checkbox_options as $checkbox_option ) {
			$this->options[ $checkbox_option ] = isset( $post_data[ $checkbox_option ] ) ? 1 : 0;
		}

		$this->options['length_type']         = $post_data['length_type'];
		$this->options['finish']              = $post_data['finish'];
		$this->options['ellipsis']            = $post_data['ellipsis'];
		$this->options['read_more']           = isset( $post_data['read_more'] ) ? $post_data['read_more'] : $this->options['read_more'];
		$this->options['allowed_tags']        = isset( $post_data['allowed_tags'] ) ? array_unique( (array) $post_data['allowed_tags'] ) : array();
		$this->options['exclude_pages']       = isset( $post_data['exclude_pages'] ) ? array_unique( (array) $post_data['exclude_pages'] ) : array();
		$this->options['allowed_tags_option'] = $post_data['allowed_tags_option'];

		update_option( 'advanced_excerpt', $this->options );

		wp_redirect( $this->get_settings_page_url( array( 'settings-updated' => '1' ) ) );
		exit;
	}

	public function get_settings_page_url( $args = array() ) {

		$args = wp_parse_args( $args, array( 'page' => 'advanced-excerpt' ) );

		return add_query_arg( $args, admin_url( 'options-general.php' ) );
	}

	public function page_options() {

		extract( $this->options, EXTR_SKIP );

		$ellipsis	= htmlentities( $ellipsis );
		$read_more	= htmlentities( $read_more );

		$tag_list = array_unique( array_merge( $this->options_basic_tags, $allowed_tags ) );
		sort( $tag_list );
		$tag_cols = 5;

		// Provides a set of checkboxes allowing the user to exclude the excerpt filter on certain page types.
		$exclude_pages_list = array(
			'front'    => __( 'Front Page', 'advanced-excerpt' ),
			'home'     => __( 'Blog Archive', 'advanced-excerpt' ),
			'feed'     => __( 'Posts RSS Feed', 'advanced-excerpt' ),
			'search'   => __( 'Search Archive', 'advanced-excerpt' ),
			'author'   => __( 'Author Archive', 'advanced-excerpt' ),
			'category' => __( 'Category Archive', 'advanced-excerpt' ),
			'tag'      => __( 'Tag Archive', 'advanced-excerpt' ),
		);

		$exclude_pages_list = apply_filters( 'advanced_excerpt_exclude_pages_list', $exclude_pages_list );

		require $this->loader->templates . 'options.php';
	}

	public function get_current_page_types() {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			return false;
		}
		$wp_query_object_vars = get_object_vars( $wp_query );

		$page_types = array();
		foreach ( $wp_query_object_vars as $key => $value ) {
			if ( false === strpos( $key, 'is_' ) ) {
				continue;
			}

			if ( true === $value ) {
				$page_types[] = str_replace( 'is_', '', $key );
			}
		}

		return $page_types;
	}
}

return new Advanced_Excerpt;
