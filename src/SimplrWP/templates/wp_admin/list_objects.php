<h1>
	<?php echo $this->options['object']->get_labels()['plural']; ?> 
	<?php if($this->options['is_manageable']): ?> 
		<a href="?page=<?php echo $this->options['object']->get_unique_name(); ?>&id=" class="page-title-action">Add New <?php echo $this->options['object']->get_labels()['singular']; ?></a>
	<?php endif; ?>
	<?php echo apply_filters('after_list_headline-' . $this->options['object']->get_unique_name(), ''); ?>
</h1>
<?php if(!empty($object_admin_list->get_options()['query_fields'] )) { ?>
<form method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php foreach($_GET as $key => $value) {
    	if(!in_array($key, ['s', 'page']))
    		echo '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
	} ?>
  	<?php $object_admin_list->search_box('Search ' . $object_admin_list->get_object()->get_labels()['plural'], 'simplrwp_q'); ?>
</form>
<?php } ?>
<form method="post">
	<?php echo $object_admin_list->display(); ?>
</form>