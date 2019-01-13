<?php

use yii\helpers\Url;
use kartik\dynagrid\DynaGrid;
use kartik\grid\GridView;
use app\models\Transactions;

$this->title = '';

?>
<div class="site-index row">
    <div class="col-md-12">
        <?php if (!Yii::$app->user->isGuest) : ?>
            <div class="row">
                <div class="col-md-12 text-center">
                    <!--                <div class="display-4"><? //= 'Witaj ' . Yii::$app->user->identity->username ?></div>-->
                    <div class="display-4">Zestawienie miesięcznych statystyk</div>
                </div>
            </div>
            <div class="row mt-5 justify-content-md-center">
                <div class="col-md-4 mr-3">
                    <?= DynaGrid::widget([
                        'columns' => [
                            [
                                'label' => '',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->name . ' <i class="fas fa-angle-double-right"></i> ' . Yii::$app->formatter->asDecimal(Transactions::expensesCategories($model->id), 2) . ' zł';
                                },
                            ],
                            [
                                'class' => 'kartik\grid\ExpandRowColumn',
                                'value' => function () {
                                    return GridView::ROW_COLLAPSED;
                                },
                                'expandIcon' => '<i class="fas fa-angle-down"></i>',
                                'collapseIcon' => '<i class="fas fa-angle-up"></i>',
                                'detailUrl' => Url::to(['/category/expand-category']),
                                'allowBatchToggle' => false,
                                'width' => '30px',
                            ],
                        ],
                        'options' => ['class' => 'month-statistic-grid'],
                        'gridOptions' => [
                            'dataProvider' => $dataProvider,
                            'toolbar' => false,
                            'striped' => false,
                            'condensed' => true,
                            'responsive' => true,
                            'panel' => [
                                'type' => 'primary',
                                'heading' => '<i class="glyphicon glyphicon-list"></i> ' . 'Wydatki na kategorie',
                                'after' => false,
                                'before' => false,
                                'footer' => false,
                            ],
                            'summary' => false,
                        ],
                    ]); ?>
                </div>
                <div class="col-md-6" style="margin-top: 3%">
                    <div class="row">
                        <div class="col-md-9 statistic-text">Zalogowany użytkownik:</div>
                        <h3><span class="badge badge-success"><?= ucfirst(Yii::$app->user->identity->username) ?></span>
                        </h3>
                    </div>
                    <div class="row">
                        <div class="col-md-9 statistic-text">Suma wydatków w tym miesiącu:</div>
                        <h3>
                            <span class="badge badge-dark"><?= Yii::$app->formatter->asDecimal(Transactions::monthlyExpenses()) ?> zł</span>
                        </h3>
                    </div>
                    <div class="row">
                        <div class="col-md-9 statistic-text">Ostatnia aktualizacja danych:</div>
                        <h3><span class="badge badge-info "><?= \app\models\User::lastUpload() ?></span></h3>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="display-4 text-center">Witaj w aplikacji <span class="text-info" ">Budżet domowy</span></div>
            <div class="text-center display-4 mt-4" style="font-size: 35px">Aby zaczać korzystać z aplikacji <span class="badge badge-success">zaloguj się</span> lub <span class="badge badge-info">zarejestruj</span></div>
        <?php endif; ?>
    </div>
</div>