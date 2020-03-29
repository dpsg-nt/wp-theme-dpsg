<form action="<?php bloginfo('url'); ?>" method="get" class="searchform">
	<div>
		<label class="screen-reader-text" for="s"><?php _e('Suche:', 'dpsg'); ?></label> 
		<input type="text" value="Suchen" onfocus="if(this.value == this.defaultValue) this.value = '';" onblur="if(!this.value) this.value = this.defaultValue;" name="s" /> 
		<input type="submit" class="searchsubmit" value="<?php _e('Suchen', 'dpsg'); ?>" />
	</div> 
</form>