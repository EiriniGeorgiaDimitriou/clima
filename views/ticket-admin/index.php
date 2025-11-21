<?php
use app\models\TicketHead;
use app\models\TicketBody;
use yii\bootstrap4\ButtonDropdown;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\TicketHeadSearch $searchModel */

// --- Styles like your new layout ---
$this->registerCss(<<<CSS
.badge-user    { background:#28a745 !important; color:#fff !important; }   /* User */
.badge-admin   { background:#007bff !important; color:#fff !important; }   /* Administrator */
.badge-closed  { background:#6c757d !important; color:#fff !important; }   /* Closed */
.badge-pill    { border-radius:10rem; padding:.35em .6em; font-weight:600; }
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
                'data-href' => Url::to(['ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'id' => $model->id, 'mode' => 0]),
                'class'     => 'grid-row',
                'style'     => 'cursor:pointer',
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
                'filterInputOptions' => ['class' => 'form-control'],
            ],
            [
                'attribute' => 'department',
                'label'     => 'Ticket category',
                'filterInputOptions' => ['class' => 'form-control'],
            ],
            [
                'attribute' => 'topic',
                'label'     => 'Ticket subject',
                'filterInputOptions' => ['class' => 'form-control'],
            ],
            [
                'attribute' => 'answersCount',
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
                        if ((int)$t['client'] === 1) { // Administrator replies
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
                'filterInputOptions' => ['class' => 'form-control'],
            ],
            [
                'attribute' => 'status',
                'label'     => 'Status',
                'format'    => 'raw',
                'value'     => static function ($model) {
                    // Old logic for who replied + closed badge
                    $parts = [];
                    if (isset($model->body['client'])) {
                        if ((int)$model->body['client'] === 0) {
                            $parts[] = Html::tag('span', 'User', ['class' => 'badge badge-pill badge-user']);
                        } elseif ((int)$model->body['client'] === 1) {
                            $parts[] = Html::tag('span', 'Administrator', ['class' => 'badge badge-pill badge-admin']);
                        }
                    }
                    if ((int)$model->status === TicketHead::CLOSED) {
                        $parts[] = Html::tag('span', 'Closed', ['class' => 'badge badge-pill badge-closed']);
                    }
                    if (empty($parts)) {
                        $parts[] = Html::tag('span', 'Unknown', ['class' => 'badge badge-pill badge-closed']);
                    }
                    return implode('&nbsp;', $parts);
                },
                'contentOptions' => ['class' => 'text-center'],
                'headerOptions'  => ['style' => 'width:180px'],
            ],
            [
                'attribute' => 'date_update',
                'label'     => 'Last updated',
                'format'    => ['datetime', 'php:Y-m-d H:i'],
                'filterInputOptions' => ['class' => 'form-control'],
                'headerOptions'  => ['style' => 'width:180px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
//            [
//                'class' => 'yii\grid\ActionColumn',
//                'header' => 'Actions',
//                'headerOptions' => ['style' => 'width:140px'],
//                'contentOptions' => ['class' => 'text-center'],
//
//                // template must include the name "answer"
//                'template' => '{answer}',
//
//                'buttons' => [
//                    'answer' => function ($url, $model) {
//                        return Html::a(
//                            'Answer',
//                            ['ticket-admin/answer', 'url1' => $_SERVER['REQUEST_URI'], 'mode' => 0, 'id' => $model->id],
//                            [
//                                'class' => 'btn btn-sm btn-primary no-row-click', // ← visible button
//                                'title' => 'Answer this ticket',
//                            ]
//                        );
//                    },
//                ],
//            ],

        ],
    ]) ?>
</div>

<?php
// Keep the “clickable row” JS
$js = <<<JS
document.addEventListener('click', function(e){
  if (e.target.closest('.no-row-click, .dropdown-menu, a, button, input, select, label')) return;
  const row = e.target.closest('.grid-row');
  if (row && row.dataset.href) window.location = row.dataset.href;
});
JS;
$this->registerJs($js);
?>
