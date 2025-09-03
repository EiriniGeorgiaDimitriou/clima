<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/** @var $model app\models\Configuration */
/** @var $pages array */

$form = ActiveForm::begin(['id' => 'general-form']);
?>

<h2>General request options</h2>

<?= $form->field($model, 'reviewer_num') ?>
<?= $form->field($model, 'schema_url') ?>
<?= $form->field($model, 'home_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>
<?= $form->field($model, 'privacy_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>
<?= $form->field($model, 'help_page')->dropDownList($pages, ['prompt' => 'Select page']) ?>

<?= Html::a('Manage pages', ['/administration/manage-pages'], ['class'=>'btn btn-secondary']) ?>

<div class="form-group mt-3">
    <?= Html::button('Save', ['class' => 'btn btn-primary', 'id' => 'save-general']) ?>
    <?= Html::a('Cancel', ['/administration/index'], ['class'=>'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>
