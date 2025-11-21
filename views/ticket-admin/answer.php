<?php
/** @var \ricco\ticket\models\TicketHead $newTicket */
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \ricco\ticket\models\TicketBody $thisTicket */
?>

<div class="panel page-block">
    <div class="container-fluid row">

        <div class="col-md-12 clearfix">

            <!-- Right: Answer + Close/Re-open + Delete -->
            <div class="pull-right">

                <!-- Answer button -->
                <?php if ($mode == 0): ?>
                    <!-- Already in answer mode: trigger collapse -->
                    <a class="btn btn-primary"
                       role="button"
                       data-toggle="collapse"
                       href="#collapseExample"
                       aria-expanded="false"
                       aria-controls="collapseExample">
                        <i class="glyphicon glyphicon-pencil"></i>
                        Write answer
                    </a>
                <?php else: ?>
                    <!-- Not in answer mode: go to answer with mode=0 -->
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-pencil"></i> Answer',
                        ['ticket-admin/answer', 'id' => $ticketHead->id, 'mode' => 0, 'url1' => $url],
                        ['class' => 'btn btn-primary', 'encode' => false]
                    ) ?>
                <?php endif; ?>

                <!-- Close / Re-open -->
                <?php if ((int)$ticketHead->status === \app\models\TicketHead::CLOSED): ?>
                    <?= Html::a(
                        'Re-open',
                        ['ticket-admin/reopen', 'id' => $ticketHead->id],
                        [
                            'class' => 'btn btn-warning',
                            'data-confirm' => 'Are you sure you want to re-open this ticket?',
                        ]
                    ) ?>
                <?php else: ?>
                    <?= Html::a(
                        'Close',
                        ['ticket-admin/closed', 'id' => $ticketHead->id],
                        [
                            'class' => 'btn btn-dark',
                            'data-confirm' => 'Are you sure you want to close this ticket?',
                        ]
                    ) ?>
                <?php endif; ?>

                <!-- Delete -->
                <?= Html::a(
                    'Delete',
                    ['ticket-admin/delete', 'id' => $ticketHead->id],
                    [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'Are you sure you want to delete this ticket?',
                    ]
                ) ?>
                <!-- Back -->
                <?= Html::a(
                    'Back',
                    ['ticket-admin/index', 'id' => $ticketHead->id],
                    [
                        'class' => 'btn btn-default',
                    ]
                ) ?>

            </div>
        </div>

            <div class="col-lg-12">
        <?php if ($mode == 0) { ?>
            <div class="collapse" id="collapseExample">
                <div class="well">
                    <?php $form = \yii\widgets\ActiveForm::begin() ?>
                    <?= $form->field($newTicket,
                        'text')->textarea(['style' => 'height: 150px; resize: none;'])->label('Message')->error() ?>
                    <div class="text-center">
                        <button class='btn btn-primary'>Submit</button>
                    </div>
                    <?= $form->errorSummary($newTicket) ?>
                    <?php $form->end() ?>
                </div>
            </div>
            <?php } ?>

            <div class="clearfix" style="margin-bottom: 20px"></div>
            <?php foreach ($thisTicket as $ticket) : ?>
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <span><?= $ticket['name_user'] ?>&nbsp;<span
                                    style="font-size: 12px">( <?= ($ticket['client'] == 1) ? 'Administrator' : 'User' ?>
                                )</span></span>
                        <span class="pull-right"><?= $ticket['date'] ?></span>
                    </div>
                    <div class="panel-body">
                        <?= nl2br(Html::encode($ticket['text'])) ?>
                        <?php if (!empty($ticket['file'])) : ?>
                            <hr>
                            <?php foreach ($ticket['file'] as $file) : ?>
                                <a href="<?= Url::to('@web/fileTicket/') . $file['fileName'] ?>" target="_blank"><img
                                            src="<?= Url::to('@web/fileTicket/reduced/') . $file['fileName'] ?> " alt="..."
                                            class="img-thumbnail"></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="row"><div class="col-md-12">Ticket opened at page:&nbsp; <?=Html::a($ticketHead->page,$ticketHead->page, ['target'=>'_blank'])?></div></div>
    </div>
</div>