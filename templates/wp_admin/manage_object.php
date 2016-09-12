<div id="poststuff">

	<?php if($this->options['is_manageable']) : ?>
		<h1><?php echo (empty($this->options['object']->get_id()) ? 'New' : 'Edit') . ' ' . $this->options['object']->get_labels()['singular']; ?></h1>
	<?php 
		else:
	?>
		<h1><?php echo 'View ' . $this->options['object']->get_labels()['singular']; ?></h1>
	<?php 
		endif;
	?>
	<form method="post">
		<input name="id" type="hidden" value="<?php echo $this->options['object']->get_id(); ?>" />
		<div id="poststuff">
			<?php do_action('add_meta_boxes'); ?>
			<div id="post-body" class="columns-2">
				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes($this->options['object']->get_unique_name(), 'side', ''); ?>
				</div>
				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes($this->options['object']->get_unique_name(), 'normal', ''); ?>
				</div>
			</div>
		</div>
	</form>
</div>
