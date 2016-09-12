<h1>
	<?php echo $this->options['object']->get_labels()['plural']; ?> 
	<?php if($this->options['is_manageable']): ?> 
		<a href="?page=<?php echo $this->options['object']->get_unique_name(); ?>&id=" class="page-title-action">Add New <?php echo $this->options['object']->get_labels()['singular']; ?></a>
	<?php endif; ?>
</h1>
<?php echo $object_admin_list->display(); ?>