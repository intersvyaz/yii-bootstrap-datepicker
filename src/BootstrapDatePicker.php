<?php

namespace Intersvyaz\Widgets;

class BootstrapDatePicker extends \CInputWidget
{
	/**
	 * Options for the bootstrap-datepicker plugin.
	 * @var array
	 */
	public $options = [];

	/**
	 * JavaScript event handlers ($event => $handler).
	 * @var array
	 */
	public $events = [];

	/**
	 * Whether to use minifed assets.
	 * @var bool
	 */
	public $minifiedAssets = true;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!isset($this->options['language'])) {
			$this->options['language'] = \Yii::app()->getLanguage();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if (isset($this->htmlOptions['id'])) {
			$id = $this->htmlOptions['id'];
		} else {
			$this->htmlOptions['id'] = $id;
		}
		if (isset($this->htmlOptions['name'])) {
			$name = $this->htmlOptions['name'];
		}

		if ($this->hasModel()) {
			echo \CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
		} else {
			echo \CHtml::textField($name, $this->value, $this->htmlOptions);
		}

		$options = !empty($this->options) ? \CJavaScript::encode($this->options) : '';

		$script = "jQuery('#{$id}').datepicker({$options})";
		foreach ($this->events as $event => $handler) {
			$script .= ".on('{$event}'," . \CJavaScript::encode($handler) . ")";
		}
		$script .= ';';

		\Yii::app()->clientScript->registerScript(__CLASS__ . '#' . $this->getId(), $script);
		$this->registerAssets();
	}

	/**
	 * Register widget assets.
	 */
	protected function registerAssets()
	{
		$assetPath = realpath(__DIR__ . '/../assets');
		$assetUrl = \Yii::app()->assetManager->publish($assetPath);
		$minifyPrefix = $this->minifiedAssets ? '.min' : '';

		\Yii::app()->clientScript
			->registerCssFile($assetUrl . '/bootstrap-datepicker' . $minifyPrefix . '.css')
			->registerScriptFile($assetUrl . '/bootstrap-datepicker' . $minifyPrefix . '.js');

		$localeFile = '/locales/bootstrap-datepicker.' . $this->options['language'] . '.js';
		if (file_exists($assetPath . $localeFile)) {
			\Yii::app()->clientScript->registerScriptFile($assetUrl . $localeFile);
		}
	}
}

