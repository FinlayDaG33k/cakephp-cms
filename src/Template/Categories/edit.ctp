<?php
$this->extend('QoboAdminPanel./Common/panel-wrapper');
$this->assign('title', __d('QoboAdminPanel', 'Categories'));
$this->assign('panel-title', __d('QoboAdminPanel', 'Edit ' . $category->name));
?>
<?= $this->Form->create($category); ?>
<fieldset>
    <?php
    echo $this->Form->input('name');
    echo $this->Form->input('slug');
    echo $this->Form->input('parent_id', ['options' => $parentCategories]);
    ?>
</fieldset>
<?= $this->Form->button(__("Save")); ?>
<?= $this->Form->end() ?>
