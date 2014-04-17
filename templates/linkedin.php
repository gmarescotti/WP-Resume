<?php 
/**
* Template for main WP Resume linkedin page
* @package WP_Resume
*/
?><div class="wp_resume_admin wrap">
   <h2><?php _e('Resume Linkedin', 'wp-resume'); ?></h2>
   <form method="post" action='options.php' id="wp_resume_form">
      <?php settings_errors(); ?>
      <?php settings_fields( 'wp_resume_linkedin' );  ?>	
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
         <!-- tr valign="top">
            <th scope="row"><label for="wp_resume_options[name]"><?php _e('Name', 'wp-resume') ;?></label></th>
            <td>
               <input name="wp_resume_options[name]" type="text" id="wp_resume_options[name]" value="<?php if ( isset( $user_options['name'] ) ) echo $user_options['name']; ?>" class="regular-text" /><BR />
               <span class="description"><?php _e('Your name -- displays on the top of your resume', 'wp-resume'); ?>.</span>
            </td>
         </tr -->
         <tr valign="top">
            <th scope="row"><?php _e('Linkedin public profile links', 'wp-resume'); ?></th>
            <td>
               <ul class="contact_info_blank" style="display:none;">
                  <?php var_dump ($this); $this->parent->template->linkedin_row( array( 'field_id' => '', 'value' => '' ) ); ?> </ul>
               <ul id="contact_info"> <?php if ( isset($user_options['contact_info'] ) && is_array( $user_options['contact_info'] ) ) 
		     array_walk_recursive($user_options['contact_info'], array( &$this->parent->admin, 'contact_info_row' ) ); ?>
               </ul>
               <a href="#" id="add_contact_field">+ <?php _e('Add Field', 'wp-resume'); ?></a><br />
               <span class="description"><?php _e('(optional) Add any contact info you would like included in your resume', 'wp-resume'); ?>.</span>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><?php _e('Resume Order', 'wp-resume'); ?></th>
            <td>
               <?php $this->parent->admin->order_dragdrop( (int) $current_author ); ?>
               <span class="description"><?php _e('New positions are automatically displayed in reverse chronological order, but you can fine tune that order by rearranging the elements in the list above', 'wp-resume'); ?>.</span>
            </td>
         </tr>
   </table>
   <p class="submit">
   <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-resume') ?>" />
   </p>
</form>
</div>
