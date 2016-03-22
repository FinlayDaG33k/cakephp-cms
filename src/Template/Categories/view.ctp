<?= $this->element('QoboAdminPanel.main-title'); ?>

<div class="panel panel-default">
    <!-- Panel header -->
    <div class="panel-heading">
        <h3 class="panel-title"><?= h($category->name) ?></h3>
    </div>
    <table class="table table-striped" cellpadding="0" cellspacing="0">
        <tr>
            <td><?= __('Id') ?></td>
            <td><?= h($category->id) ?></td>
        </tr>
        <tr>
            <td><?= __('Slug') ?></td>
            <td><?= h($category->slug) ?></td>
        </tr>
        <tr>
            <td><?= __('Name') ?></td>
            <td><?= h($category->name) ?></td>
        </tr>
        <tr>
            <td><?= __('Created') ?></td>
            <td><?= h($category->created) ?></td>
        </tr>
        <tr>
            <td><?= __('Modified') ?></td>
            <td><?= h($category->modified) ?></td>
        </tr>
    </table>
</div>
<div class="panel panel-default">
    <!-- Panel header -->
    <div class="panel-heading">
        <h3 class="panel-title"><?= __('Related Articles') ?></h3>
    </div>
    <?php if (!empty($category->articles)): ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Title') ?></th>
                <th><?= __('Slug') ?></th>
                <th><?= __('Excerpt') ?></th>
                <th><?= __('Content') ?></th>
                <th><?= __('Category') ?></th>
                <th><?= __('Created By') ?></th>
                <th><?= __('Modified By') ?></th>
                <th><?= __('Publish Date') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($category->articles as $articles): ?>
                <tr>
                    <td><?= h($articles->id) ?></td>
                    <td><?= h($articles->title) ?></td>
                    <td><?= h($articles->slug) ?></td>
                    <td><?= h($articles->excerpt) ?></td>
                    <td><?= h($articles->content) ?></td>
                    <td><?= h($articles->category) ?></td>
                    <td><?= h($articles->created_by) ?></td>
                    <td><?= h($articles->modified_by) ?></td>
                    <td><?= h($articles->publish_date) ?></td>
                    <td><?= h($articles->created) ?></td>
                    <td><?= h($articles->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link('', ['controller' => 'Articles', 'action' => 'view', $articles->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']) ?>
                        <?= $this->Html->link('', ['controller' => 'Articles', 'action' => 'edit', $articles->id], ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                        <?= $this->Form->postLink('', ['controller' => 'Articles', 'action' => 'delete', $articles->id], ['confirm' => __('Are you sure you want to delete # {0}?', $articles->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="panel-body">no related Articles</p>
    <?php endif; ?>
</div>