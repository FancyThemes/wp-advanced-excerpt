<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php
		_e( "Advanced Excerpt Options", 'advanced-excerpt' );
		?></h2>
	<form method="post" action="">
	<?php
		if ( function_exists( 'wp_nonce_field' ) )
			wp_nonce_field( 'advanced_excerpt_update_options' );
?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="length">
				<?php _e( "Excerpt Length:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="length" type="text"
						   id="length"
						   value="<?php echo $length; ?>" size="2"/>
					<input name="use_words" type="checkbox"
						   id="use_words" value="on"<?php
		echo ( 1 == $use_words ) ? ' checked="checked"' : ''; ?>/>
						   <?php _e( "Use words?", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="ellipsis">
				<?php _e( "Ellipsis:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="ellipsis" type="text"
						   id="ellipsis"
						   value="<?php echo $ellipsis; ?>" size="5"/>
					<?php _e( '(use <a href="http://www.w3schools.com/tags/ref_entities.asp">HTML entities</a>)', 'advanced-excerpt' ); ?>
					<br />
					<?php _e( "Will substitute the part of the post that is omitted in the excerpt.", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="length">
				<?php _e( "Finish:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="finish_word" type="checkbox"
						   id="finish_word" value="on"<?php
		echo ( 1 == $finish_word ) ? ' checked="checked"' : ''; ?>/>
						   <?php _e( "Word", 'advanced-excerpt' ); ?><br/>
					<input name="finish_sentence" type="checkbox"
						   id="finish_sentence" value="on"<?php
		echo ( 1 == $finish_sentence ) ? ' checked="checked"' : ''; ?>/>
						   <?php _e( "Sentence", 'advanced-excerpt' ); ?>
					<br />
					<?php _e( "Prevents cutting a word or sentence at the end of an excerpt. This option can result in (slightly) longer excerpts.", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="read_more">
				<?php  _e( "&lsquo;Read-more&rsquo; Text:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="read_more" type="text"
						   id="read_more" value="<?php echo $read_more; ?>" />
					<input name="add_link" type="checkbox"
						   id="add_link" value="on" <?php
		echo ( 1 == $add_link ) ? 'checked="checked" ' : ''; ?>/>
						   <?php _e( "Add link to excerpt", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="no_custom">
				<?php _e( "No Custom Excerpts:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="no_custom" type="checkbox"
						   id="no_custom" value="on" <?php
		echo ( 1 == $no_custom ) ? 'checked="checked" ' : ''; ?>/>
						   <?php _e( "Generate excerpts even if a post has a custom excerpt attached.", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="no_shortcode">
				<?php _e( "Strip Shortcodes:", 'advanced-excerpt' ); ?></label></th>
				<td>
					<input name="no_shortcode" type="checkbox"
						   id="no_shortcode" value="on" <?php
		echo ( 1 == $no_shortcode ) ? 'checked="checked" ' : ''; ?>/>
						   <?php _e( "Remove shortcodes from the excerpt. <em>(recommended)</em>", 'advanced-excerpt' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( "Keep Markup:", 'advanced-excerpt' ); ?></th>
				<td>
					<table id="tags_table">
						<tr>
							<td colspan="<?php echo $tag_cols; ?>">
	<input name="allowed_tags[]" type="checkbox"
		   value="_all" <?php echo ( in_array( '_all', $allowed_tags ) ) ? 'checked="checked" ' : ''; ?>/>
		   <?php _e( "Don't remove any markup", 'advanced-excerpt' ); ?>
							</td>
						</tr>
<?php
		$i = 0;
		foreach ( $tag_list as $tag ):
			if ( $tag == '_all' )
				continue;
			if ( 0 == $i % $tag_cols ):
?>
						<tr>
<?php
				endif;
			$i++;
?>
							<td>
	<input name="allowed_tags[]" type="checkbox"
		   value="<?php echo $tag; ?>" <?php
		echo ( in_array( $tag, $allowed_tags ) ) ? 'checked="checked" ' : ''; ?>/>
	<code><?php echo $tag; ?></code>
							</td>
<?php
		if ( 0 == $i % $tag_cols ):
			$i = 0;
		echo '</tr>';
		endif;
		endforeach;
		if ( 0 != $i % $tag_cols ):
?>
						  <td colspan="<?php echo $tag_cols - $i; ?>">&nbsp;</td>
						</tr>
<?php
		endif;
?>
					</table>
					<a href="" id="select_all">Select all</a>
					/ <a href="" id="select_none">Select none</a><br />
					More tags:
					<select name="more_tags" id="more_tags">
<?php
		foreach ( self::$options_all_tags as $tag ):
?>
						<option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
<?php
		endforeach;
?>
					</select>
					<input type="button" name="add_tag" id="add_tag" class="button" value="Add tag" />
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="Submit" class="button-primary"
								 value="<?php _e( "Save Changes", 'advanced-excerpt' ); ?>" /></p>
	</form>
</div>