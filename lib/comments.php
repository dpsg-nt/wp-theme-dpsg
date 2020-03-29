<?php
/**
 * Renders a single comments; Called for each comment
 */
function crb_render_comment($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment-entry">
			<div class="comment-author vcard">
				<?php echo get_avatar($comment, 48); ?>
			</div>
			<?php if ($comment->comment_approved == '0') : ?>
				<em class="moderation-notice"><?php _e('Your comment is awaiting moderation.') ?></em><br />
			<?php endif; ?>
			
			<div class="comment-text">
				<h4><?php

					comment_author_link();

					$id = get_comment(get_comment_ID())->user_id;

					if($id) :

						$user_url = get_the_author_meta( 'user_url', $id );

						if($user_url) :

							?><a class="link-root" href="<?php echo $user_url; ?>" target="_blank"><i class="icon-home2"></i></a><?php
						endif;

					endif;

				?></h4>
				<p><strong><?php comment_date(); ?>:</strong> <?php echo get_comment_text(); ?></p>
			</div>
	
			<div class="comment-reply">
				<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
			</div>
			<div class="cl">&nbsp;</div>
		</div>
	<?php
}

/**
 * Restricts direct access to the comments.php and checks whether the comments are password protected.
 * @return boolean
 */
function crb_comments_restrict_access() {
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if ( post_password_required() ) {
		echo '<p class="nocomments">This post is password protected. Enter the password to view comments.</p>';
		return false;
	}

	return true;
}

/**
 * Renders all current comments
 * @param  callable $callback
 */
function crb_comments_render_list($callback) {
	?>
	<?php if ( have_comments() ) : ?>
		<h3><?php comments_number('No Responses', 'One Response', '% Responses' );?></h3>
		<ol class="commentlist">
			<?php wp_list_comments('callback=' . $callback); ?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="alignleft"><?php previous_comments_link() ?></div>
				<div class="alignright"><?php next_comments_link() ?></div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php if ( comments_open() ) : ?>
			<!-- If comments are open, but there are no comments. -->
		<?php else : // comments are closed ?>
			<p class="nocomments">Comments are closed.</p>
		<?php endif; ?>
	<?php endif; ?>
	<?php
}

/**
 * Comment form hooks:
 *  - comment_form_before
 *  - comment_form_must_log_in_after
 *  - comment_form_top
 *  - comment_form_logged_in_after
 *  - comment_notes_before
 *  - comment_form_before_fields
 *  - comment_form_field_{$name} (a filter on each and every field, where {$name} is the key name of the field in the array)
 *  - comment_form_after_fields
 *  - comment_form_field_comment (a filter on the “comment_field” default setting, which contains the textarea for the comment)
 *  - comment_form (action hook after the textarea, for backward compatibility mainly)
 *  - comment_form_after
 *  - comment_form_comments_closed
 * 
 * Comment form arguments:
 *  - 'fields'			   => apply_filters( 'comment_form_default_fields', $fields ),
 *  - 'comment_field'		=> '<p class="comment-form-comment">...',
 *  - 'must_log_in'		  => '<p class="must-log-in">...',
 *  - 'logged_in_as'		 => '<p class="logged-in-as">...',
 *  - 'comment_notes_before' => '<p class="comment-notes">...',
 *  - 'comment_notes_after'  => '<dl class="form-allowed-tags">...',
 *  - 'id_form'			  => 'commentform',
 *  - 'id_submit'			=> 'submit',
 *  - 'title_reply'		  => __( 'Leave a Reply' ),
 *  - 'title_reply_to'	   => __( 'Leave a Reply to %s' ),
 *  - 'cancel_reply_link'	=> __( 'Cancel reply' ),
 *  - 'label_submit'		 => __( 'Post Comment' ),
 * 
 * Reference: http://codex.wordpress.org/Function_Reference/comment_form
 */
function crb_comments_render_form($arguments) {
	comment_form($arguments);
	return false;
}
