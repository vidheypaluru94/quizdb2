<?php

namespace app\controllers;

use app\models\Data;
use app\models\Presentquiz;
use app\models\QuestionForm;
use app\models\Quizsetter;
use Yii;
use app\models\Questions;
use app\models\Questionssearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\Pagination;
use app\models\Results;
use app\models\Quiz;
use yii\db\Expression;
use yii\web\UploadedFile;


/**
 * QuestionsController implements the CRUD actions for Questions model.
 */
class QuestionsController extends Controller
{
    public function behaviors()
    {
        return [

        'access'=>[
            'class'=>AccessControl::classname(),
            'only'=>['create','update','index','view','search','form','quizattempt'],
            'rules'=>[
            [
                'allow'=>true,
                'roles'=>['@']]]
            ],

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Questions models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $searchModel = new Questionssearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$id);

        return $this->render('index', [
            'id'=>$id,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAjaxattempt($id)
    {
      //return 'cjeck';
      $model = new Presentquiz();

      //$model=$this->findModel($id);

      if ($model->load(Yii::$app->request->post())) {
          $temp=Yii::$app->request->post();
          $temp = $temp['Presentquiz'];
          $model->option1 = $temp['option1'];
          $model->option2 = $temp['option2'];
          $model->option3 = $temp['option3'];
          $model->option4 = $temp['option4'];
          $model->option5 = $temp['option5'];

          $model->userid=Yii::$app->user->identity['username'];
          $model->attempted=1;
          $model->quizid=$id;
          $model->questionid=$temp['questionid'];

          if ($model->validate()) {

             $query1 = Questions::find()->where(['quizid' => $id]);

              $pagination = new Pagination([
                  'defaultPageSize' => 1,
                  'totalCount' => $query1->count(),
              ]);
              //print_r($pagination->offset);
              //exit();
              //$pagination->setPage(1);
              $query1 = $query1->offset($pagination->offset)->limit($pagination->limit)->all();

              $query2=Presentquiz::find()->where(['quizid'=> $id,'questionid'=>$model->questionid,'userid'=>$model->userid])->one();
              if($query2!=NULL){
                  $query2->delete();
              }

              $model->save();

          }
      }

      $out['status'] = "success";
      $out['val'] = '';
      json_encode($out,TRUE);

    }


    public function actionQuizattempt($id)
    {

        $username=Yii::$app->user->identity['username'];
        $tuple=Quiz::find()->where(['quizid'=>$id])->one();
        if($tuple['mattempt']=='0'&& Results::find()->where(['quizid'=>$id,'userid'=>$username])->count()){
            return $this->render('//site/error', [
                'message'=>"User can only attempt a quiz once",
                'name'=>"Unauthorized for students",
            ]);
        }

        $result=Results::find()->where(['userid'=>$username,'quizid'=>$id])->one();
        if($result==NULL){
            $result=new Results();
            $result->userid=$username;
            $result->quizid=$id;
            $result->save(false);
        }



        $model = new Presentquiz();
        //$model=$this->findModel($id);





        if ($model->load(Yii::$app->request->post())) {
            $temp=Yii::$app->request->post();
            $temp = $temp['Presentquiz'];
            $model->option1 = $temp['option1'];
            $model->option2 = $temp['option2'];
            $model->option3 = $temp['option3'];
            $model->option4 = $temp['option4'];
            $model->option5 = $temp['option5'];

            $model->userid=Yii::$app->user->identity['username'];
            $model->attempted=1;
            $model->quizid=$id;
            $model->questionid=$temp['questionid'];
            //exit();
            if ($model->validate()) {
                //$query1=$tem['query1'];
         //       $query1 = Questions::find()->where(['quizid' => $id]);
                //$query1 = (object) json_decode(base64_decode($_POST['info']));

                $query1 = Questions::find()->where(['quizid' => $id]);
       //         $query1=Data::$query1;

        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query1->count(),
        ]);

 // print_r($pagination->offset);
   //             exit();
//$pagination->setPage(1);
            $query1 = $query1->offset($pagination->offset)->limit($pagination->limit)->all();




    //            $model->attempted=1;

//            print_r($model);
//exit();


                $query2=Presentquiz::find()->where(['quizid'=> $id,'questionid'=>$model->questionid,'userid'=>$model->userid])->one();
                if($query2!=NULL){
                    $query2->delete();
                }

                $model->save();
                $query2=Presentquiz::find()->where(['quizid'=> $id,'questionid'=>$model->questionid,'userid'=>$model->userid])->one();

                $date=Quiz::find()->where(['quizid'=>$id])->one();
                $date=$date['endtime'];
                $datetime=$date;

                $date1= date('Y-m-d H:i:s', time()+60*60*5+30*60);
                $diff = max(0,(strtotime($datetime) -strtotime($date1)));
                if($diff==0){
                    return $this->actionSubmission($id);
                }
            return $this->render('quizattempt', [
                'maindata' => $query1,
                'model' => $model,
                'default' => $query2,
                'datetime'=>$date,
                'option' => $tuple['options'],
                'pagination' => $pagination,
                'result' =>$result,
                'number'=>$number,
            ]);



                // form inputs are valid, do something here
            }
           /* if ($result = $model->upload()) {
                $query1 = Questions::find()->where(['quizid' => $id])->asArray()->all();


                return $this->render('quizattempt', [
                    'maindata' => $query1,
                    'model' => $model,

                ]);

            }
            else{
                print_r($model);
                exit();
            }
           */
        }
        if(array_key_exists( 'endtest',Yii::$app->request->post())){
                    $query1 = Presentquiz::find()->indexBy('questionid')->where(['quizid' => $id,'userid'=>Yii::$app->user->identity['username']])->asArray()->all();
                    $query2= Questions::find()->indexBy('questionid')->where(['quizid'=>$id])->asArray()->all();

                    //print_r($query1);
                    //exit();


                   $score=0;
                   $correct=0;
                   $wrong=0;

                    foreach ($query1 as $key => $value) {
                        $ans=0;
                        $flag=0;

                       if(  $value['option1']==1)
                       {
                        if($query2[$key]['weight1']<0 )
                            $flag=$query2[$key]['weight1'];
                       }

                        $ans+=$query2[$key]['weight1']*$value['option1'];

                        if($query2[$key]['weight2']<0 && $value['option2']==1 )
                       {
                            $flag=$query2[$key]['weight2'];
                       }

                        $ans+=$query2[$key]['weight2']*$value['option2'];

                        if($query2[$key]['weight3']<0 && $value['option3']==1 )
                       {
                            $flag=$query2[$key]['weight3'];
                       }

                        $ans+=$query2[$key]['weight3']*$value['option3'];

                         if($query2[$key]['weight4']<0 && $value['option4']==1 )
                       {
                            $flag=$query2[$key]['weight4'];
                       }

                        $ans+=$query2[$key]['weight4']*$value['option4'];

                       if($query2[$key]['weight5']<0 && $value['option5']==1 )
                       {
                            $flag=$query2[$key]['weight5'];
                       }

                        $ans+=$query2[$key]['weight5']*$value['option5'];

                        if($flag!=0){
                            $ans=$flag;
                        }
                        if($ans>0){
                            $correct++;
                        }
                        else{
                            $wrong++;
                        }
                        $tem = Presentquiz::find()->where(['questionid'=>$key,'userid'=>Yii::$app->user->identity['username'],'quizid'=>$id])->one();
                        $tem->attempted=$ans;
                        $tem->update();


                        $score=$score+$ans;


                    }

                    $queryquiz = Quiz::find()->where(['quizid'=>$id])->one();
                    $queryresult=new Results;
                    $queryresult->totalscore=$queryquiz->totalscore;
                    $queryresult->obtainedscore=$score;
                    $queryresult->totalquestions=$queryquiz->totalquestions;
                    $queryresult->correctattempted=$correct;
                    $queryresult->wrongattempted=$wrong;
                    $queryresult->quizname=$queryquiz->quizname;

                    $queryresult->userid=Yii::$app->user->identity['username'];
                    $queryresult->quizid=$id;
                    $queryresult->save();
                    //print($score);
                    //exit();
                return $this->render('submission', [
                'queryresult' => $queryresult,
            ]);




        }
        else {






            $query1 = Questions::find()->where(['quizid' => $id]);

            $exchange=base64_encode(json_encode($query1));

            $number=count($query1->all());

        $pagination = new Pagination([
            'defaultPageSize' => 1,
            'totalCount' => $query1->count(),
        ]);

            //print_r($pagination->offset);
            //exit();
//$pagination->setPage(1);
            $query1 = $query1->offset($pagination->offset)->limit($pagination->limit)->all();
            //Data::$query1=$query1;
            //print_r($query1);
            //exit();

            //print_r($query1);
   //         exit();

            $date=Quiz::find()->where(['quizid'=>$id])->one();
                $date=$date['endtime'];
                $datetime=$date;


            $date1= date('Y-m-d H:i:s', time()+60*60*5+30*60);
            $diff = max(0,(strtotime($datetime) -strtotime($date1)));
            if($diff==0){
                return $this->actionSubmission($id);
            }


            return $this->render('quizattempt', [
                'maindata' => $query1,
                'model' => $model,
                'datetime'=>$date,
                'option' => $tuple['option'],
                'pagination' => $pagination,
                'result' =>$result,
                'number'=>$number,

            ]);
        }
    }


    /**
     * Displays a single Questions model.
     * @param integer $quizid
     * @param integer $questionid
     * @return mixed
     */
    public function actionView($quizid, $questionid)
    {
        return $this->render('view', [
            'model' => $this->findModel($quizid, $questionid),
            'id'=>$quizid,
        ]);
    }

    /**
     * Creates a new Questions model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $model = new Questions();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');


            if($model->file) {
                //echo "am here";
               // exit();
                $imagepath = 'uploads/questions';
                $model->image = $imagepath.rand(10,100).$model->file->name;

            }
            else 
                $model->image="no image";
            if($model->save()){
                if($model->file) {
                    $model->file->saveAs($model->image);
                    return $this->redirect(['view', 'quizid' => $model->quizid, 'questionid' => $model->questionid]);
                } 
                else
                    return $this->redirect(['view', 'quizid' => $model->quizid, 'questionid' => $model->questionid]);
                
            }
            else
                echo "not saving";
        } else {
            return $this->render('create', [
                'id'=>$id,
                'model' => $model,
            ]);
        }
    }
    
    public function actionSubmission($id)
    {
        print_r($id);
        $query1 = Presentquiz::find()->indexBy('questionid')->where(['quizid' => $id,'userid'=>Yii::$app->user->identity['username']])->asArray()->all();
        $query2= Questions::find()->indexBy('questionid')->where(['quizid'=>$id])->asArray()->all();

        //print_r($query1);
        //exit();


        $score=0;
        $correct=0;
        $wrong=0;

        foreach ($query1 as $key => $value) {
            $ans=0;
            $flag=0;

            if(  $value['option1']==1)
            {
                if($query2[$key]['weight1']<0 )
                    $flag=$query2[$key]['weight1'];
            }

            $ans+=$query2[$key]['weight1']*$value['option1'];

            if($query2[$key]['weight2']<0 && $value['option2']==1 )
            {
                $flag=$query2[$key]['weight2'];
            }

            $ans+=$query2[$key]['weight2']*$value['option2'];

            if($query2[$key]['weight3']<0 && $value['option3']==1 )
            {
                $flag=$query2[$key]['weight3'];
            }

            $ans+=$query2[$key]['weight3']*$value['option3'];

            if($query2[$key]['weight4']<0 && $value['option4']==1 )
            {
                $flag=$query2[$key]['weight4'];
            }

            $ans+=$query2[$key]['weight4']*$value['option4'];

            if($query2[$key]['weight5']<0 && $value['option5']==1 )
            {
                $flag=$query2[$key]['weight5'];
            }

            $ans+=$query2[$key]['weight5']*$value['option5'];

            if($flag!=0){
                $ans=$flag;
            }
            if($ans>0){
                $correct++;
            }
            else{
                $wrong++;
            }
            $tem = Presentquiz::find()->where(['questionid'=>$key,'userid'=>Yii::$app->user->identity['username'],'quizid'=>$id])->one();
            $tem->attempted=$ans;
            $tem->update();


            $score=$score+$ans;


        }

        $queryquiz = Quiz::find()->where(['quizid'=>$id])->one();
        $queryresult=new Results;
        $queryresult->totalscore=$queryquiz->totalscore;
        $queryresult->obtainedscore=$score;
        $queryresult->totalquestions=$queryquiz->totalquestions;
        $queryresult->correctattempted=$correct;
        $queryresult->wrongattempted=$wrong;
        $queryresult->quizname=$queryquiz->quizname;
        $userid=Yii::$app->user->identity['username'];
        $queryresult->userid=Yii::$app->user->identity['username'];
        $queryresult->quizid=$id;

        $result=Results::find()->where(['quizid'=>$id,'userid'=>$userid])->one();
        if(!$result) {
            $queryresult->save(false);
        }
        else{

            $result->totalscore=$queryquiz->totalscore;
            $result->obtainedscore=$score;
            $result->totalquestions=$queryquiz->totalquestions;
            $result->correctattempted=$correct;
            $result->wrongattempted=$wrong;
            $result->quizname=$queryquiz->quizname;
            $result->save(false);
        }
        //print($queryresult->quizname);
        //exit();
        return $this->render('submission', [
            'queryresult' => $queryresult,
            'id'=>$id,
        ]);

    }

    /**
     * Updates an existing Questions model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $quizid
     * @param integer $questionid
     * @return mixed
     */
    public function actionUpdate($quizid, $questionid)
    {
        $model = $this->findModel($quizid, $questionid);

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            
            if($model->file) {

                $imagepath = 'uploads/questions';
                $model->image = $imagepath.rand(10,100).$model->file->name;

            }

            if($model->save()){
                if($model->file) {
                    $model->file->saveAs($model->image);
                    return $this->redirect(['view', 'quizid' => $model->quizid, 'questionid' => $model->questionid]);
                } 
                else
                    return $this->redirect(['view', 'quizid' => $model->quizid, 'questionid' => $model->questionid]);
                
            }

        }
        else {
            return $this->render('update', [
                'model' => $model,
                'id'=>$quizid,
            ]);
        }
    }

    /**
     * Deletes an existing Questions model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $quizid
     * @param integer $questionid
     * @return mixed
     */
    public function actionDelete($quizid, $questionid)
    {
        $this->findModel($quizid, $questionid)->delete();

        return $this->actionIndex($quizid);
    }

    /**
     * Finds the Questions model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $quizid
     * @param integer $questionid
     * @return Questions the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($quizid, $questionid)
    {
        if (($model = Questions::findOne(['quizid' => $quizid, 'questionid' => $questionid])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }




    public function actionDeleteImage($id)
    {
        $image = Questions::find()->where(['id' => $id])->one()->image;
        if($image){
            if(!unlink($image)){
                return false;
            }
        }
        $questions = Questions::findOne($id);
        $questions->image =NULL;
        $questions->update();
        return $this->redirect(['update','id'=>$id]);
    } 
}

