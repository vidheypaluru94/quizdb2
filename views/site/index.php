



<?php

use yii\helpers\Html;
use app\models\Data;
/* @var $this yii\web\View */


?>

<div class="site-index">


    <div class="body-content">
<?php
        if(Yii::$app->user->identity['role']=='student') {







            $url = Data::$url."site/newquiz";
            $this->title = 'My Yii Application';
            //echo Html::a('Challenges', $url, ['class' => 'btn btn-success']);
            echo "<br><br><br>";

            //echo Html::a('Tutorials', $url, ['class' => 'btn btn-success']);
            echo "<br><br><br>";

            //echo Html::a('Problemset', $url, ['class' => 'btn btn-success']);

?>

            <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Challenges</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href=<?php echo $url ?>>Challenges &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Tutorials</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href=$url>Tutorials &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Problemset</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href=$url>Problemset &raquo;</a></p>
            </div>
        </div>

    </div>







































<?php





        }
        if(Yii::$app->user->identity['role']=='setter') {
            $url = Data::$url."quiz/index";
            $this->title = 'My Yii Application';
            echo Html::a('Managequizes', $url, ['class' => 'btn btn-success']);



        }

if(Yii::$app->user->identity['role']=='admin') {
            $url = Data::$url."quiz/index";
            $this->title = 'My Yii Application';
            echo Html::a('Manage-Challenges', $url, ['class' => 'btn btn-success']);
            echo "<br>";
            echo "<br>";
            echo "<br>";

            $url = Data::$url."users/index";
            $this->title = 'My Yii Application';
            echo Html::a('Manage-Users', $url, ['class' => 'btn btn-success']);


        }



?>
    </div>
</div>