<?php
use app\models\TicketHead;
use app\models\TicketBody;
use yii\bootstrap4\ButtonDropdown;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\TicketHeadSearch $searchModel */

$statusFilter = [
    TicketHead::OPEN   => 'Open',
    TicketHead::WAIT   => 'Waiting',
    TicketHead::ANSWER => 'Answered',
    TicketHead::CLOSED => 'Closed',
];


$badgeMap = [
    TicketHead::OPEN   => ['text' => 'Open',     'class' => 'badge-open'],
    TicketHead::WAIT   => ['text' => 'Waiting',  'class' => 'badge-waiting'],
    TicketHead::ANSWER => ['text' => 'Answered', 'class' => 'badge-answered'],
    TicketHead::CLOSED => ['text' => 'Closed',   'class' => 'badge-closed'],
    TicketHead::VIEWED => ['text' => 'Unknown',   'class' => 'badge-unknown'],
];
$this->registerCss(<<<CSS
.badge-open     { background:#007bff !important; color:#fff !important; } /* blue */
.badge-waiting  { background:#fd7e14 !important; color:#fff !important; } /* orange */
.badge-answered { background:#28a745 !important; color:#fff !important; } /* green */
.badge-closed   { background:#dc3545 !important; color:#fff !important; } /* red */
.badge-unknown  { background:#ffc107 !important; color:#ffff !important; } /* yellow */
.badge-pill { border-radius:10rem; padding:.35em .6em; font-weight:600; }
CSS);
?>
<div class="panel page-block">
    <div class="row mb-3">
        <div class="col-md-2">
            <?= Html::a('Open new ticket', ['ticket-admin/open'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'summary'      => 'Showing <b>{begin}-{end}</b> of <b>{totalCount}</b> items.',
        'rowOptions' => static function ($model) {
            return [
                'data-href' => Url::to(['ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'id' => $model->id, 'mode' => 1]),
                'class' => 'grid-row',
                'style' => 'cursor:pointer', // no background color
            ];
        },

        'columns' => [
            [
                'attribute' => 'username',
                'label'     => 'Username',
                'format'    => 'raw',
                'value'     => static function ($m) {
                    $u = $m->userName ? $m->userName->username : '';
                    return Html::tag('strong', Html::encode(explode('@', (string)$u)[0]));
                },
                'filterInputOptions' => ['class' => 'form-control', 'placeholder' => ''],
            ],
            [
                'attribute' => 'department',
                'label'     => 'Ticket category',
                'filterInputOptions' => ['class' => 'form-control', 'placeholder' => ''],
            ],
            [
                'attribute' => 'topic',
                'label'     => 'Ticket subject',
                'filterInputOptions' => ['class' => 'form-control', 'placeholder' => ''],
            ],
            [
                'label' => 'No of answers',
                'value' => static function ($model) {
                    $tickets = TicketBody::find()
                        ->where(['id_head' => $model->id])
                        ->joinWith('file')
                        ->orderBy(['date' => SORT_DESC])
                        ->asArray()
                        ->all();

                    $answers = 0;
                    foreach ($tickets as $t) {
                        if ((int)$t['client'] === 1) { // Administrator
                            $answers++;
                        }
                    }
                    if (count($tickets) === $answers) {
                        $answers--;
                    }
                    return max($answers, 0);
                },
                'contentOptions' => ['class' => 'text-center'],
                'headerOptions'  => ['style' => 'width:140px'],
                'filter'         => false,
            ],
            [
                'attribute' => 'status',
                'label'     => 'Status',
                'format'    => 'raw',
                'value'     => function ($model) use ($badgeMap) {
                    $s = is_numeric($model->status) ? (int)$model->status : null;
                    $conf = $badgeMap[$s] ?? ['text' => 'Unknown', 'class' => 'badge-unknown']; // <- yellow fallback
                    return \yii\helpers\Html::tag('span', $conf['text'], [
                        'class' => 'badge badge-pill ' . $conf['class'],
                    ]);
                },
                'filter' => \yii\helpers\Html::activeDropDownList(
                    $searchModel,
                    'status',
                    [
                        TicketHead::OPEN   => 'Open',
                        TicketHead::WAIT   => 'Waiting',
                        TicketHead::ANSWER => 'Answered',
                        TicketHead::CLOSED => 'Closed',
                        TicketHead::VIEWED => 'Unknown',
                    ],
                    ['class' => 'form-control', 'prompt' => '']
                ),
                'contentOptions' => ['class' => 'text-center'],
                'headerOptions'  => ['style' => 'width:160px'],
            ],




            [
                'attribute' => 'date_update',
                'label'     => 'Last updated',
                'format'    => ['datetime', 'php:Y-m-d H:i'],
                'filterInputOptions' => ['class' => 'form-control', 'placeholder' => ''],
                'headerOptions' => ['style' => 'width:180px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'header' => 'Actions',
                'contentOptions' => ['class' => 'text-center'],
                'buttons' => [
                    'actions' => static function ($url, $model) {
                        $items = [
                            [
                                'label' => 'View',
                                'url'   => ['ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'id' => $model->id, 'mode' => 1],
                                'linkOptions' => ['class' => 'dropdown-item no-row-click'],
                            ],
                            [
                                'label' => 'Answer',
                                'url'   => ['ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'mode' => 0, 'id' => $model->id],
                                'linkOptions' => ['class' => 'dropdown-item no-row-click'],
                            ],
                        ];

                        if ((int)$model->status === TicketHead::CLOSED) {
                            $items[] = [
                                'label' => 'Re-open',
                                'url'   => ['ticket-admin/reopen', 'id' => $model->id],
                                'linkOptions' => [
                                    'class' => 'dropdown-item text-warning no-row-click',
                                    'data-confirm' => 'Are you sure you want to re-open the ticket?',
                                ],
                            ];
                        } else {
                            $items[] = [
                                'label' => 'Close',
                                'url'   => ['ticket-admin/closed', 'id' => $model->id],
                                'linkOptions' => [
                                    'class' => 'dropdown-item no-row-click',
                                    'data-confirm' => 'Are you sure you want to close the ticket?',
                                ],
                            ];
                        }

                        $items[] = [
                            'label' => 'Delete',
                            'url'   => ['ticket-admin/delete', 'id' => $model->id],
                            'linkOptions' => [
                                'class' => 'dropdown-item text-danger no-row-click',
                                'data-confirm' => 'Are you sure you want to delete the ticket?',
                            ],
                        ];

                        return ButtonDropdown::widget([
                            'label' => 'Actions',
                            'options' => ['class' => 'btn btn-sm btn-outline-primary no-row-click'],
                            'dropdown' => [
                                'items' => $items,
                                'options' => ['class' => 'dropdown-menu dropdown-menu-right'],
                            ],
                            'encodeLabel' => false,
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width:140px'],
            ],
        ],
    ]) ?>
</div>

<?php
// Make entire row clickable, but ignore clicks on interactive controls (dropdown, links, inputs)
$js = <<<JS
document.addEventListener('click', function(e){
  if (e.target.closest('.no-row-click, .dropdown-menu, a, button, input, select, label')) return;
  const row = e.target.closest('.grid-row');
  if (row && row.dataset.href) window.location = row.dataset.href;
});
JS;
$this->registerJs($js);
?>
