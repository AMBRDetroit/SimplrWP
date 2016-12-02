<div id="minor-publishing">
	<?php 
		$object_data = $this->options['object']->get();
		if(isset($object_data['created_at'])) {
	?>
		<div class="timestamp--createdAt misc-pub-section curtime misc-pub-curtime">
			<span id="timestamp">Created on <?php echo date_i18n(get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' ), strtotime($object_data['created_at']) ); ?></span>
		</div>
	<?php 
		}
		if(isset($object_data['updated_at'])) {
	?>
		<div class="timestamp--updatedAt misc-pub-section curtime misc-pub-curtime">
			<span id="timestamp">Updated on <?php echo date_i18n(get_option( 'date_format' ) . ' \a\t ' . get_option( 'time_format' ), strtotime($object_data['updated_at']) ); ?></span>
		</div>
	<?php 
		}
		if(empty($object_data['id'])) { ?>
		<div class="misc-pub-section curtime misc-pub-curtime">
			Saving will create a new <?php echo strtolower($this->options['object']->get_labels()['singular']); ?>.
		</div>
	<?php 
		}
	?>
</div>	
<?php 
	// show save and delete options if display_add_new_button setting is set to true
	if($this->options['is_manageable']):
?>
<div id="major-publishing-actions">
	<?php if(!empty($object_data['id'])) { ?>
		<div id="delete-action" class="submitbox">
			<a onclick="return confirm('Are you sure you want to delete this <?php echo strtolower($this->options['object']->get_labels()['singular']); ?>?')" class="submitdelete deletion" href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $this->options['object']->get_unique_name() . '&id=' . $this->options['object']->get()['id'] . '&delete'?>">Delete</a>
		</div>
	<?php } ?>
	<div id="publishing-action">
		<input name="save" id="save" class="button button-primary button-large" value="Save" type="submit">
	</div>
	<div class="clear"></div>
</div>
<?php endif;?>
