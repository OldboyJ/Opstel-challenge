# Opstel-challenge

Opstel Services Coding Challenge
================================
Prerequisites:
===================
•	Know a little PHP, mySQL or similar, HTML, JavaScript, and maybe some boot strap

•	Know how GET, POST, PUT work in a web application

•	Know how to connect to an API using PHP

Requirements:
=============
•	Windows, Linux, or Mac Box (Know your system!)

•	Web server (IIS, Apache ect.)

•	PHP installed on web server (preferably version 7.0)

•	Have a local database like mySQL or Postgre

Assumptions:
==============
You have your webserver up and serving PHP pages and a database running

For quick start I recommend XAMPP https://www.apachefriends.org/index.html

Backend Challenge
=================

<b>Goal:</b> To create a restful API for a front-end application to connect to

<b>Step 1: </b>Install Composer
--------------------------------------
Composer is a package installer not unlike NPM for JavaScript

Go to the following site and install composer on your system

https://getcomposer.org/

<b>Step 2:</b> Make composer a global command for your systems shell
-----------------------------------------------------------------------

<b>Step 3:</b>  Install Yii2 Basic App using composer
----------------------------------------------------------------
For instructions view the link below

 http://www.yiiframework.com/doc-2.0/guide-start-installation.html

<b>Step 4: </b>connect your application to your database
--------------------------------------------------------------
<b>HINT:</b> Change your db config file

<b>Step 5:</b>  Set up the project to be an API backend
----------------------------------------------------------
You can follow the quick start guide here

http://www.yiiframework.com/doc-2.0/guide-start-installation.html

good tutorial

https://code.tutsplus.com/tutorials/programming-with-yii2-building-a-restful-api--cms-27513

google -creating a restful web service with yii2

<b>Step 6:</b> Create a user table in your database
-------------------------------------------------------
check out how to do this with migrations here
http://www.yiiframework.com/doc-2.0/guide-db-migrations.html

<b>Hint:</b> create a migration similar to
```php
        //Create User Table
        $this->createTable('user', [
            'id' => $this->primaryKey(11),
            'first_name' => $this->string(50)->notNull(),
            'last_name' => $this->string(50)->notNull(),
            'employee_id' => $this->string(64)->notNull()->unique(),
            'email' => $this->string(200)->notNull(),
            'tenant' => $this->integer(3)->notNull(),
            'username' => $this->string(255)->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255)->notNull(),
            'group' => $this->integer(3),
            'state' => $this->string(2)->notNull(),
            'use_external_auth' => $this->boolean()->defaultValue(false),
            'external_id' => $this->string(255),
            'created_at' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer(11),
            'last_login' => $this->integer(11),
        ] );
        //Index User Table
        $this->createIndex( 'idx_user_id', 'user', 'id');
        $this->createIndex( 'idx_user_first_name', 'user', 'first_name');
        $this->createIndex( 'idx_user_last_name', 'user', 'last_name');
        $this->createIndex( 'idx_user_tenant', 'user', 'tenant');
        $this->createIndex( 'idx_user_username', 'user', 'username');
        $this->createIndex( 'idx_user_employee_id', 'user', 'employee_id');

```

<b>Step 7:</b> Create a restful API CRUD for a user
----------------------------------------------------
Use GII to create the model for a user (follow instructions here)
http://www.yiiframework.com/doc-2.0/guide-start-gii.html

Follow instructions here to make the Controller
http://www.yiiframework.com/doc-2.0/guide-rest-controllers.html

This is what it should look like
```php
<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use app\models\User;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     *The checkAccess() method will be called by the default actions of
     *yii\rest\ActiveController. If you create new actions and also want to
     *perform access check, you should call this method explicitly in the new actions.
     */

    public $appRoles=[
        "agent"=>"agent",
        "manager"=>"manager",
        "admin"=>"admin",
        "opstel"=>"opstel",
    ];

    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
//            if ($model->author_id !== \Yii::$app->user->id)
//                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }

    public function behaviors()
    {
        return [
            'basicAuth' => [
                'class' => HttpBasicAuth::className(),
                'except' => ['create', 'login', 'resetpassword'],
            ],
        ];
    }


    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'indexDataProvider'];
        return $actions;
    }

    public function indexDataProvider()
    {
        $params = \Yii::$app->request->queryParams;

        $model = new  User();
        // I'm using yii\base\Model::getAttributes() here
        // In a real app I'd rather properly assign
        // $model->scenario then use $model->safeAttributes() instead
        $modelAttr = $model->attributes;

        // this will hold filtering attrs pairs ( 'name' => 'value' )
        $search = [];

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                // In case if you don't want to allow wired requests
                // holding 'objects', 'arrays' or 'resources'
                if (!is_scalar($key) or !is_scalar($value)) {
                    throw new BadRequestHttpException('Bad Request');
                }
                // if the attr name is not a reserved Keyword like 'q' or 'sort' and
                // is matching one of models attributes then we need it to filter results
                if (!in_array(strtolower($key), $this->reservedParams)
                    && ArrayHelper::keyExists($key, $modelAttr, false)) {
                    $search[$key] = $value;
                }
            }
        }
        $query = User::find()->where($search);
        // you may implement and return your 'ActiveDataProvider' instance here.
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $provider;

    }

    public function actionRoleslist()
    {
        return $this->appRoles;
    }

    public function actionGetrole(){
        $request = Yii::$app->request;
        $queryString=$request->queryParams;
        if(isset($queryString["myuser"])) {
            $userID = $queryString["myuser"];
            $auth = \Yii::$app->authManager;
            $roles = $auth->getRolesByUser($userID);
            foreach ($roles as $key=>$value){
                $myRole=$key;
            }
            if(isset($myRole)) return $myRole;
            return "none";
        }else{
            throw new \yii\web\ForbiddenHttpException(sprintf('You must supply a user ID', 'getrole'));
        }

    }

    public function actionAssignrole(){
        // all roles in app
        $roles=$this->appRoles;

        //get user sent vars
        $request = Yii::$app->request;
        $queryString=$request->queryParams;

//        //make sure correct vars sent for request
        if(isset($queryString["myuser"]) && isset($queryString["myrole"])){
            $userID=$queryString["myuser"];
            $myRole=$queryString["myrole"];
            $auth = \Yii::$app->authManager;

            //revmove previous roles
            $auth->revokeAll($userID);
            $assignRole = $auth->getRole($myRole);
            $auth->assign($assignRole, $userID);

            return $myRole;

        }else{
            throw new \yii\web\ForbiddenHttpException(sprintf('You must supply a user ID and role', 'assignrole'));
            //return json_encode($queryString);
        }
    }

    public function actionLogin()
    {
        /**
         * This is the model class for table "user".
         *
         * integer $id #
         * string $first_name #
         * string $last_name#
         * string $employee_id
         * string $email
         * integer $tenant
         * string $username
         * string $auth_key
         * string $password_hash
         * string $password_reset_token
         * integer $group
         * string $state
         * boolean $use_external_auth
         * string $external_id
         * integer $created_at
         * integer $updated_at
         * integer $last_login
         */
        $request = Yii::$app->request;
        $params = $request->bodyParams;
        if(!isset($params["username"])||!isset($params["password"])){
            throw new \yii\web\ForbiddenHttpException(sprintf('incorrect credentials.', 'login'));
        }else{
            $model = User::findOne(["username" => $params["username"]]);
        }

        if (empty($model)) {
            throw new \yii\web\ForbiddenHttpException(sprintf('incorrect credentials.', 'login'));
        }

        if ($model->validatePassword($params["password"])) {
            $model->last_login = Yii::$app->formatter->asTimestamp(date_create());
            $model->save(false);
            $myResponse= [
              'auth_key'=>$model->auth_key,
              'id'=>$model->id,
              'first_name'=>$model->first_name,
              'last_name'=>$model->last_name,
              'employee_id'=> $model->employee_id,
              'email'=> $model-> email,
              'tenant'=>$model->tenant,
              'group'=>$model->group,
            ];
            //$myResponse= json_encode($myResponse);
            return $myResponse;
        } else {
            throw new \yii\web\ForbiddenHttpException(sprintf('incorrect credentials.', 'login'));
        }
    }


}
```

Front End Challenge
==========================================================

<b>Goal:</b> Create a front-end GUI that connects to your previously made back end restful API

<b>Step1:</b> Create a new Yii2 project using composer
-----------------------------------------------------

<b>Step2 (optional):</b> Install the guzzle
-------------------------------------------
for instructions and docs go here
http://docs.guzzlephp.org/en/stable/

Alternatively, you can use CURL or something similar to make calls to your back-end API


<b>Step3:</b> Create a model that uses guzzle or CURL to make calls to the back-end restful API
------------------------------------------------------------------------------------------------
<b>Hint:</b> create a base model that does CRUD and extend your model from that
if you use yii2 guzzle extention it would look similar to this
```php
<?php
namespace app\models;

use yii;
use yii\base\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use yii\data\ArrayDataProvider;

class APIModel extends Model
{
    public $baseUrl = 'http://localhost:8080';
    Public $uri;

    public function getAuthKey(){
        // Establish Session and Authkey Access
        $session = Yii::$app->session;
        // check if a session is already open
        if (!$session->isActive){
            $session->open();
        }

        return $session['auth_key'];
    }

    public function save(){
        // Create Guzzle Client
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
        ]);

        if($this->validate())
        {
            $authKey= $this->getAuthKey();
            // Attempt API Transmission
            try {
                $headers=[
                    'auth' => [$authKey, 'null'],
                    'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json','connection'=>'close',],
                    'json' => $this,
                ];

                $reBody=[
                    'json' => $this
                ];
                $response = $client->request('POST', $this->uri,$headers,$reBody);
                $body = $response->getBody();
                return $body;
            }
            catch (RequestException $e) {
                //echo "Exception...";
                //print_r($stream->getContents());
                //print_r($body);
                //echo Psr7\str($e->getRequest());
                if ($e->hasResponse()) {
                    $response = (string)$e->getResponse()->getBody();
                    //$stream = Psr7\str($response);
                    //var_dump($response);
                    $arrResponse = json_decode($response);
                    foreach($arrResponse as $idx => $errorData)
                    {
                        $this->addError($errorData->field,$errorData->message);
                    }

                }

            }

        }

    }

    public function getAllModels($pageSize=10){

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
        ]);
        $authKey= $this->getAuthKey();
        //remove the try catch to view the actual error
        try {
            $headers=[
                'auth' => [$authKey, 'null'],
                'headers'  => ['connection'=>'close',],
            ];
            $body='';
            $response = $client->request('GET',  $this->uri,$headers, $body);
            $body = $response->getBody();
            $myBody=\GuzzleHttp\json_decode($body,true);

        } catch (RequestException $e)
        {
            $myBody= Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                $myBody= Psr7\str($e->getResponse());
            }
            $myBody= "code: '".$e->getCode()."'' request: '".$myBody."'";
        }

        if(is_array ($myBody)) {
            $dataProvider = new ArrayDataProvider([
                'allModels' => $myBody,
                'sort' => [
                    'attributes' => ['id', 'username', 'employee_id'],
                ],
                'pagination' => [
                    'pageSize' => $pageSize,
                ],
            ]);
        }else{
            $dataProvider=null;
        }

        return $dataProvider;
    }

    public function Search($query){

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
        ]);
        $authKey= $this->getAuthKey();
        //remove the try catch to view the actual error
        try {
            $headers=[
                'auth' => [$authKey, 'null'],
                'headers'  => ['connection'=>'close',],
                'query' => $query,
            ];
            $body='';
            $response = $client->request('GET',  $this->uri,$headers, $body);
            $body = $response->getBody();
            $myBody=\GuzzleHttp\json_decode($body,true);

        } catch (RequestException $e)
        {
            $myBody= Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                $myBody= Psr7\str($e->getResponse());
            }
            $myBody= "code: '".$e->getCode()."'' request: '".$myBody."'";
        }

        //this was test data
        //$searchModel = new UserQuery();
        //$dataProvider = $searchModel->search(Yii::$app->request->queryParams, $this->_owner);
        if(is_array ($myBody)) {
            $dataProvider = new ArrayDataProvider([
                'allModels' => $myBody,
                'sort' => [
                    'attributes' => ['id', 'username', 'employee_id'],
                ],
                'pagination' => [
                    'pageSize' => $pageSize,
                ],
            ]);
        }else{
            $dataProvider=null;
        }

        return $dataProvider;
    }

    public function getByID($id){

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
        ]);
        $authKey= $this->getAuthKey();
        $headers=[
            'auth' => [$authKey, 'null'],
            'headers'  => ['connection'=>'close',],
        ];
        $body='';
        $response = $client->request('GET', $this->uri.'/'.$id,$headers, $body);
        $body = $response->getBody();
        $body=\GuzzleHttp\json_decode($body,true);

        return $body;
    }

    public function update($id){

        // Create Guzzle Client
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->baseUrl,
        ]);

        if($this->validate())
        {
            $authKey= $this->getAuthKey();
            // Attempt API Transmission
            try {
                $headers=[
                    'auth' => [$authKey, 'null'],
                    'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json','connection'=>'close',],
                    'json' => $this
                ];

                $reBody=[
                    'json' => $this
                ];
                $response = $client->request('PUT', $this->uri.'/'.$id,$headers,$reBody);
                $body = $response->getBody();
                return $body;
            }
            catch (RequestException $e) {
                //echo "Exception...";
                //print_r($stream->getContents());
                //print_r($body);
                //echo Psr7\str($e->getRequest());
                if ($e->hasResponse()) {
                    $response = (string)$e->getResponse()->getBody();
                    //$stream = Psr7\str($response);
                    //var_dump($response);
                    $arrResponse = json_decode($response);
                    foreach($arrResponse as $idx => $errorData)
                    {
                        $this->addError($errorData->field,$errorData->message);
                    }

                }

            }

        }

    }

}
```

<b>Step4:</b> create a Yii2 Active Form to create a new user in the database
----------------------------------------------------------------------------

Check out the following
http://www.yiiframework.com/doc-2.0/guide-input-forms.html

Stretch Challenge
===============================

<b>Goal:</b>  Create a login page that after you log in displays all the users for the app

<b>Step1:</b> Configure the back-end API to use BASIC Auth credentials
----------------------------------------------------------------------

<b>Step2:</b> make a user login form on the front-end app that authenticates the user to the back-end API using basic auth
----------------------------------------------------------------------------------------------------------------------------

<b>Step3:</b> After Authentication display a page that lists all users in the app using a yii2 grid view  
---------------------------------------------------------------------------------------------------------

Submission Instructions
============================

Create a new repo on GitHub for both front-end and back end applications. Push your code and send a link to the repo to Development@opstel.com
