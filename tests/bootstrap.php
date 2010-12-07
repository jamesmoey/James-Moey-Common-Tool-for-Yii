<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../../../yii/framework/yiit.php';
$config=dirname(__FILE__).'/../../../config/test.php';

defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yiit);

Yii::createWebApplication($config);

Yii::import('ext.common.components.*');