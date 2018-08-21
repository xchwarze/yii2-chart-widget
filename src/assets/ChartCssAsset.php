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
class ChartCssAsset extends \yii\web\AssetBundle {
	/**
	 * @var string the directory that contains the source asset files for this asset bundle.
	 */
	public $sourcePath = '@bower';

	/**
	 * @var array list of CSS files that this bundle contains.
	 */
	public $css = [
		'chartist/dist/chartist.min.css'
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
