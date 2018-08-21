<?php
/**
 * @package yii2-widget-chart
 * @author Xchwarze <xchwarze@gmail.com>
 */

namespace exocet\yii2\chart;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * This widget is an Implementation of chartist.js for yii2 framework ([[http://gionkunz.github.io/chartist-js/]])
 *
 * ```php
 * Chart::widget([
 *      'type'              => Chart::TYPE_LINE,
 *      'labels'            => [1, 2, 3, 4],
 *      'series'            => [[100, 120, 180, 200]],
 *      'clientOptions'     => [
 *          'showLine' => false,
 *          'axisX'    => [
 *              'labelInterpolationFnc' => new JsExpression('function(value, index) {
 *                  return index % 13 === 0 ? \'W\' + value : null;
 *              }')
 *          ]
 *      ],
 *      'responsiveOptions' => [
 *          'screen and (min-width: 640px)' => [
 *              'axisX' => [
 *                  'labelInterpolationFnc' => new JsExpression('function(value, index) {
 *                      return index % 4 === 0 ? \'W\' + value : null;
 *                  }')
 *              ]
 *          ]
 *      ]
 * ]);
 * ```
 *
 * @author Xchwarze <xchwarze@gmail.com>
 *
 * @property string $type
 * @property array $clientOptions Additional chartist js options ([[http://gionkunz.github.io/chartist-js/api-documentation.html]])
 */
class Chart extends \yii\base\Widget {
	/**
	 * Line chart type constant
	 */
	const TYPE_LINE = 'Line';
	/**
	 * Bar chart type constant
	 */
	const TYPE_BAR = 'Bar';
	/**
	 * Pie chart type constant
	 */
	const TYPE_PIE = 'Pie';

	/**
	 * @var array Allowed chart types
	 */
	protected $allowedTypes = [self::TYPE_LINE, self::TYPE_BAR, self::TYPE_PIE];

	/**
	 * @var string Chart type (one of class [[TYPE_*]] constants)
	 */
	private $_type = self::TYPE_LINE;

	/**
	 * @var array the options for the underlying JS plugin.
	 */
	public $clientOptions = [];
	
	/**
	 * @var array the event handlers for the underlying JS plugin.
	 */
	public $clientEvents = [];
	
	/**
	 * @var array the HTML attributes for the widget container tag.
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $options = [];

	/**
	 * @var string[] Chart labels (x-axis).
	 */
	public $labels;

	/**
	 * @var array Chart data with series to use in chart.
	 */
	public $series;

	/**
	 * @var string Ajax url for retrive data.
	 */
	public $ajax;

	/**
	 * @var bool Disable css assets.
	 */
	public $disableCss = false;

	/**
	 * @var array Specify an array of responsive option arrays which are a media query and options object pair
	 *
	 * ```php
	 * [
	 *      'screen and (min-width: 640px)' => [
	 *          'axisX' => [
	 *              'labelInterpolationFnc' => new JsExpression('function(value, index) {
	 *                  return index % 4 === 0 ? \'W\' + value : null;
	 *              }')
	 *          ]
	 *      ]
	 * ]
	 * ```
	 */
	public $responsiveOptions = [];

	/**
	 * @var boolean Show chart legend or not. (defaults to false)
	 */
	public $legend = false;

	/**
	 * @var array Legend plugin options ([[https://github.com/CodeYellowBV/chartist-plugin-legend]])
	 */
	public $legendOptions = [];

	/**
	 * @var boolean Show chart label or not. (defaults to false)
	 */
	public $label = false;

	/**
	 * @var array Legend plugin options ([[https://github.com/gionkunz/chartist-plugin-pointlabels]])
	 */
	public $labelOptions = [];

	/**
	 * {@inheritdoc}
	 * @throws InvalidConfigException
	 */
	public function init() {
		if (empty($this->ajax) && (empty($this->labels) || empty($this->series))) {
			throw new InvalidConfigException('Labels and series attributes are required');
		}

		parent::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function run() {
		Html::addCssClass($this->options, 'yii-widget-chart');
		$container = Html::tag('div', '', $this->options);

		$this->registerPlugin();

		return $container;
	}

	/**
	 * Setter function for $type
	 *
	 * @param string $type Chart type (one of class [[TYPE_*]] constants)
	 *
	 * @throws InvalidConfigException
	 */
	public function setType($type) {
		if (!in_array($this->_type, $this->allowedTypes)) {
			throw new InvalidConfigException("Type must be one of the following: ".implode(', ', $this->allowedTypes));
		}
		$this->_type = $type;
	}

	/**
	 * Getter function for $type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function registerPlugin($pluginName = null) {
		$view = $this->view;
		$id   = $this->options['id'];
		$varName = 'chart_' . str_replace('-', '_', $id);

		\exocet\yii2\chart\assets\ChartAsset::register($view);
		if (!$this->disableCss) {
			\exocet\yii2\chart\assets\ChartCssAsset::register($view);
		}

		if ($this->legend) {
			\exocet\yii2\chart\assets\ChartAssetLegend::register($view);
			$this->clientOptions = ArrayHelper::merge($this->clientOptions, [
				'plugins' => [new JsExpression('Chartist.plugins.legend('.Json::htmlEncode($this->legendOptions).')')]
			]);
		}

		if ($this->label) {
			\exocet\yii2\chart\assets\ChartAssetLabel::register($view);

			if (empty($this->labelOptions)) {
				// fix ctPointLabels v0.6.2 bug
				$this->labelOptions = new JsExpression('{ labelInterpolationFnc: function(value) { return value === undefined ? 0 : value; } }');
			}

			$this->clientOptions = ArrayHelper::merge($this->clientOptions, [
				'plugins' => [new JsExpression('Chartist.plugins.ctPointLabels('.Json::htmlEncode($this->labelOptions).')')]
			]);
		}

		$js = "var {$varName} = false;\n";
		if ($this->ajax) {
			$js .= "$.ajax({
					url: '{$this->ajax}',
					success: function(data) {					
						var {$varName} = new Chartist.{$this->type}(
							'#{$id}',
							data,
							".Json::htmlEncode($this->clientOptions).", 
							".Json::htmlEncode($this->responsiveOptions)."
						);
					}
				});
			";
		} else {
			$js .= "var {$varName} = new Chartist.{$this->type}(
				'#{$id}', 
				".Json::htmlEncode([
					'labels' => $this->labels,
					'series' => $this->series
				]).",
				".Json::htmlEncode($this->clientOptions).", 
				".Json::htmlEncode($this->responsiveOptions)."
			);";
		}

		$view->registerJs($js);
		$this->registerClientEvents();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function registerClientEvents() {
		if (!empty($this->clientEvents)) {
			$js = [];
			foreach ($this->clientEvents as $event => $handler) {
				$js[] = "chart.on('$event', $handler);";
			}
			
			$this->view->registerJs(implode("\n", $js));
		}
	}
}
