<?php 
/**
 * Template for download linkedin profile into wp resume site
 * @package WP_Resume
 */
?><label class="screen-reader-text" for="linkedin_url"><?php _e('Linkedin', 'wp-resume'); ?></label>
<input type="text" name="linkedin_url" size="34" id="linkedin_url" value="<?php echo $post->linkedin_url; ?>">
<p>
	Paste here your linkedin.com public profile and click to DOWNLOAD!
</p>
