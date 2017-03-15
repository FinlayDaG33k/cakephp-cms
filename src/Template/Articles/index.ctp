<?php
$this->loadHelper('Burzum/FileStorage.Image');

echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min'
    ],
    [
        'block' => 'scriptBotton'
    ]
);
echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({});',
    ['block' => 'scriptBotton']
);
?>
<section class="content-header">
    <h1>Articles
        <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->Form->button(
                    '<i class="fa fa-plus"></i> ' . __('Add'),
                    [
                        'type' => 'button',
                        'title' => __('Add'),
                        'class' => 'btn btn-default dropdown-toggle',
                        'data-toggle' => 'dropdown',
                        'aria-haspopup' => 'true',
                        'aria-expanded' => 'false'
                    ]
                ) ?>
                <ul class="dropdown-menu dropdown-menu-right">
                <?php foreach ($sites as $site) : ?>
                    <li>
                        <a href="<?= $this->Url->build(['action' => 'add', $site->slug]); ?>">
                            <?= $site->name ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
                <thead>
                    <tr>
                        <th><?= __('Title'); ?></th>
                        <th><?= __('Slug'); ?></th>
                        <th><?= __('Site'); ?></th>
                        <th><?= __('Category'); ?></th>
                        <th><?= __('Author'); ?></th>
                        <th><?= __('Publish'); ?></th>
                        <th><?= __('Featured Image'); ?></th>
                        <th class="actions"><?= __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article) : ?>
                    <tr>
                        <td><?= h($article->title) ?></td>
                        <td><?= h($article->slug) ?></td>
                        <td>
                        <?php if ($article->has('site')) : ?>
                            <a href="<?= $this->Url->build(['controller' => 'Sites', 'action' => 'view', $article->site->id])?>" class="label label-primary">
                                <?= h($article->site->name); ?>
                            </a>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php if ($article->has('category')) : ?>
                            <a href="<?= $this->Url->build(['controller' => 'Categories', 'action' => 'view', $article->site->slug, $article->category->slug])?>" class="label label-primary">
                                <?= h($article->category->name); ?>
                            </a>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php if ($article->has('author')) : ?>
                            <a href="<?= $this->Url->build(['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'view', $article->author->id])?>" class="label label-primary">
                                <?= h($article->author->username) ?>
                            </a>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?php if ($article->publish_date < new DateTime('now')) : ?>
                            <span class="fa fa-check" aria-hidden="true"></span>
                        <?php else : ?>
                            <span class="fa fa-remove" aria-hidden="true"></span>
                        <?php endif; ?>
                        </td>
                        <td>
                        <?=
                            isset($article->article_featured_images[0])
                            ? $this->Image->display($article->article_featured_images[0], 'small', ['width' => 30])
                            : __d('cms', 'No featured image');
                        ?>
                        </td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                                <?= $this->Html->link(
                                    '<i class="fa fa-eye"></i>',
                                    ['action' => 'view', $article->site->slug, $article->slug],
                                    ['title' => __('View'), 'class' => 'btn btn-default', 'escape' => false]
                                ) ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['action' => 'edit', $article->site->slug, $article->slug],
                                    ['title' => __('Edit'), 'class' => 'btn btn-default', 'escape' => false]
                                ) ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    ['action' => 'delete', $article->site->slug, $article->slug],
                                    [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $article->title),
                                        'title' => __('Delete'),
                                        'class' => 'btn btn-default',
                                        'escape' => false
                                    ]
                                ) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>