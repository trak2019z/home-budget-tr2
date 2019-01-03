<?php

namespace app\controllers;

use app\models\Category;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Transactions;
use app\models\CsvImporter;
use app\models\search\TransactionsSearch;

class TransactionsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Upload to database new transactions from .csv file
     *
     * @return string|Response
     */
    public function actionIndex()
    {
        $model = new Transactions();

        if ($model->load(Yii::$app->request->post())) {
            $executionStartTime = microtime(true);

            $importer = new CsvImporter($_FILES['Transactions']['tmp_name']['file'], false, ';');
            $data = $importer->get();
            $insertData = $model->migrateToBase($data);

            $executionEndTime = microtime(true);
            $seconds = round($executionEndTime - $executionStartTime, '2');

            Yii::$app->session->setFlash('success', "Dane zostały zaimportowane prawidłowo. Wykonanie skryptu zajęło {$seconds} sek. <br> Ilość dodanych rekordów: <strong>{$insertData}</strong>");
            return $this->redirect(Yii::$app->request->baseUrl . '/index.php' . '/transactions/index');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Create new transaction
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Transactions();

        if ($model->load(Yii::$app->request->post())) {
            $model->id_user = Yii::$app->user->id;
            if ($model->save()) {
                Yii::$app->session->setFlash('alert', [
                    'type' => 'success',
                    'title' => 'Informacja',
                    'message' => 'Transakcja dodana z powodzeniem',
                    'options' => ['class' => 'alert-success']
                ]);

                return $this->redirect('finances');
            }
        }

        return $this->renderAjax('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Update transaction
     *
     * @param $id integer
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id = null)
    {
        //update category via editable widget
        if (is_null($id)) {
            $data = Yii::$app->request->post();
            $categoryId = current($data['Transactions'])['category_id'];

            $model = Transactions::find()->where(['id' => $data['editableKey']])->one();
            $categoryName = Category::find()->where(['id' => $categoryId])->one();

            $model->category_id = $categoryId;
            if ($model->save()) {

                return Json::encode(['output' => $categoryName->name, 'message' => '']);
            }
        }

        //update other case
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('alert', [
                    'type' => 'success',
                    'title' => 'Informacja',
                    'message' => 'Transakcja została zaktualizowana z powodzeniem',
                    'options' => ['class' => 'alert-success']
                ]);

                return $this->redirect('finances');
            }
        }

        return $this->renderAjax('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Display all transactions
     *
     * @return string
     */
    public function actionFinances()
    {
        $searchModel = new TransactionsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('finances', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Find model
     *
     * @param $id integer
     * @return Transactions|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Transactions::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Delete transaction
     * @param $id integer
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('alert', [
            'type' => 'success',
            'title' => 'Informacja',
            'message' => 'Transakcja została usunięta z powodzeniem',
            'options' => ['class' => 'alert-success']
        ]);

        return $this->redirect(['finances']);
    }
}