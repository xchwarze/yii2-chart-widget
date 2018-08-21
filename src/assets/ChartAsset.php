<?php
/**
 * @package yii2-widget-chart
 * @author Xchwarze <xchwarze@gmail.com>
 */

namespace exocet\yii2\chart\assets;


/**
 * Asset bundle for chart widget
 *
 * @author Xchwarze <xchwarze@gmail.com>
 */
class ChartAsset extends \yii\web\AssetBundle {
	/**
	 * @var string the directory that contains the source asset files for this asset bundle.
	 */
	public $sourcePath = '@bower';

	/**
	 * @var array list of JavaScript files that this bundle contains.
	 */
	public $js = [
		'chartist/dist/chartist.min.js'
	];

	/**
	 * @var array list of bundle class names that this bundle depends on.
	 */
	public $depends = [
		'yii\web\JqueryAsset'
	];

	/**
	 * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
	 * is being published.
	 */
	public $publishOptions = [
		'only' => [
			'chartist/dist/*'
		],
		'forceCopy' => YII_DEBUG
	];
}
