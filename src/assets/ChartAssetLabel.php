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
class ChartAssetLabel extends \yii\web\AssetBundle {
	
	/**
	 * @inheritdoc
	 */
	public $sourcePath = __DIR__ . '/';

	/**
	 * @inheritdoc
	 */
	public $js = [
		// version 0.6.2 - 2018
		'js/chartist-plugin-pointlabels.min.js'
	];
}
