<?php
use Cake\Utility\Inflector;

$elements = [];
foreach ($articles as $article) {
    $element = 'Plugin/Cms/' . Inflector::camelize($article->type) . '/list';

    // fallback to plugin's element
    if (!$this->elementExists($element)) {
        $element = Inflector::camelize($article->type) . '/list';
    }

    // fallback to default element
    if (!$this->elementExists($element)) {
        $element = 'Common/list';
    }

    $elements[] = [
        'name' => $element,
        'data' => [
            'types' => $types,
            'article' => $article
        ]
    ];
}
?>
<div class ="row">
<?php foreach ($elements as $element) : ?>
    <div class="col-xs-12 col-md-4 col-lg-3">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-<?= $types[$element['data']['article']->type]['icon'] ?>"></i>
                <h3 class="box-title"><?= $element['data']['article']->title ?></h3>
                <div class="box-tools pull-right">
                    <?= $this->Html->link('<i class="fa fa-pencil"></i>', '#', [
                        'title' => __('Edit'),
                        'class' => 'btn btn-box-tool',
                        'escape' => false,
                        'data-toggle' => 'modal',
                        'data-target' => '#' . $element['data']['article']->slug
                    ]) ?>
                </div>
            </div>
            <div class="box-body">
                <?= $this->element($element['name'], $element['data']); ?>
            </div>
            <div class="box-footer small">
                <div class="row">
                    <div class="col-xs-4">
                    </div>
                    <div class="col-xs-4 text-center">
                        <?= $this->Html->link(__('MORE'), [
                            'controller' => 'Articles',
                            'action' => 'view',
                            $element['data']['article']->site->slug,
                            $element['data']['article']->slug
                        ]) ?>
                    </div>
                    <div class="col-xs-4 text-muted text-right">
                        <?= $element['data']['article']->publish_date->format('Y-m-d H:m') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php foreach ($articles as $article) : ?>
    <div class="modal fade" id="<?= $article->slug ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $article->slug ?>Label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="<?= $article->slug ?>Label">
                        <?= __('Edit') ?> <?= $article->title ? $article->title : Inflector::humanize($article->type) ?>
                    </h4>
                </div>
                <div class="modal-body">
                    <?= $this->element('Articles/post', [
                        'url' => [
                            'controller' => 'Articles',
                            'action' => 'edit',
                            $article->site->slug,
                            $article->type,
                            $article->slug
                        ],
                        'article' => $article,
                        'typeOptions' => $articleTypes[$article->type]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>