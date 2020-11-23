<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    //public $basePath = '@webroot';
    public $baseUrl = '@web';
    //public $sourcePath = "@bower/backend/";
    public $css = [
         //'bootstrap/css/bootstrap.css',
    ];
    public $js = [
    ];
	public $jsOptions = array(
		'position' => \yii\web\View::POS_HEAD
	);
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
