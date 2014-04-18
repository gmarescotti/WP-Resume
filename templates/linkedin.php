<?php 
/**
* Template for main WP Resume linkedin page
* @package WP_Resume
*/
?><div class="wp_resume_admin wrap">
   <h2><?php _e('Resume Linkedin', 'wp-resume'); ?></h2>
   <form method="post" action='options.php' id="wp_resume_form">
      <?php settings_errors(); ?>
      <?php settings_fields( 'wp_resume_options_linkedin' );  ?>	
      <table class="form-table">
	 <tr valign="top">
	    <th scope="row"><?php _e('Usage', 'wp-resume'); ?></label></th>
	    <td>
	       <strong><?php _e('To load linkedin profile into WP Resume...', 'wp-resume'); ?></strong>
	       <ol>
	          <li><?php _e('Paste your linkedin public profile in the box below', 'wp-resume'); ?></li>
	          <li><?php _e('If you wish, add as many languages as you wish - if you have in your linkedin profile', 'wp-resume'); ?></li>
	          <li><?php _e('Click on download to get your linkedin profile and see a preliminary result', 'wp-resume'); ?>.</li>
	          <li><?php _e('Click on Apply to store all data into WP Resume database', 'wp-resume'); ?>.</li>
	       </ol>
	    </td>
         </tr>
         <tr valign="top">
            <th scope="row"><label for="wp_resume_options_linkedin[linkedin_link1]"><?php _e('Link', 'wp-resume') ;?></label></th>
            <td>
               <input name="wp_resume_options_linkedin[linkedin_link1]" type="text" id="wp_resume_options_linkedin[linkedin_link1]" value="<?php if ( isset( $user_options['linkedin_link1'] ) ) echo $user_options['linkedin_link1']; ?>" class="regular-text" /><BR />
               <span class="description"><?php _e('Your linkedin public profile -- must be accesible by all', 'wp-resume'); ?>.</span>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><label for="wp_resume_options_linkedin[linkedin_link2]"><?php _e('Link #2', 'wp-resume') ;?></label></th>
            <td>
               <input name="wp_resume_options_linkedin[linkedin_link2]" type="text" id="wp_resume_options_linkedin[linkedin_link2]" value="<?php if ( isset( $user_options['linkedin_link2'] ) ) echo $user_options['linkedin_link2']; ?>" class="regular-text" /><BR />
               <span class="description"><?php _e('Your 2nd language linkedin public profile -- leave blank if you don\t have one', 'wp-resume'); ?>.</span>
            </td>
         </tr>
   </table>
   <p class="submit">
   <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-resume') ?>" />
   </p>
</form>
</div>
