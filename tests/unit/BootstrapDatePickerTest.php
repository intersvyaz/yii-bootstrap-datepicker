<?php

use \Intersvyaz\Widgets\BootstrapDatePicker;

/**
 * @coversDefaultClass \Intersvyaz\Widgets\BootstrapDatePicker
 */
class BootstrapDatePickerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param BootstrapDatePicker $widget
	 * @return string
	 */
	public function runAndCapture($widget)
	{
		$widget->init();
		ob_start();
		$widget->run();
		return ob_get_clean();
	}

	/**
	 * @return BootstrapDatePicker
	 */
	public function makeWidget()
	{
		return new BootstrapDatePicker();
	}

	/**
	 * @covers ::init
	 */
	public function testInit()
	{
		$widget = $this->makeWidget();
		$widget->init();
		$this->assertArrayHasKey('language', $widget->options);
		$this->assertNotEmpty($widget->options['language']);
	}

	/**
	 * @covers ::run
	 */
	public function testRun_FieldRenderingWithAttribute()
	{
		$widget = $this->makeWidget();
		$widget->init();
		$widget->name = 'attr';
		$widget->value = 'val';
		$widget->htmlOptions = ['foo' => 'bar'];

		$widgetOutput = $this->runAndCapture($widget);
		$this->assertTag([
			'tag' => 'input',
			'attributes' => [
				'type' => 'text',
				'foo' => 'bar',
				'name' => 'attr',
				'value' => 'val',
				'id' => 'attr'
			]
		], $widgetOutput);

		// custom input id attribute
		$widget->htmlOptions['id'] = 'baz';
		$widgetOutput = $this->runAndCapture($widget);
		$this->assertTag([
			'tag' => 'input',
			'attributes' => [
				'foo' => 'bar',
				'id' => 'baz'
			]
		], $widgetOutput);
	}

	/**
	 * @covers ::run
	 */
	public function testRun_FieldRenderingWithModel()
	{
		$widget = $this->makeWidget();
		$widget->init();
		$widget->model = new FakeModel();
		$widget->attribute = 'login';
		$widget->model->login = 'val';
		$widget->htmlOptions = ['foo' => 'bar'];

		$widgetOutput = $this->runAndCapture($widget);
		$this->assertTag([
			'tag' => 'input',
			'attributes' => [
				'type' => 'text',
				'foo' => 'bar',
				'name' => 'FakeModel[login]',
				'value' => 'val',
				'id' => 'FakeModel_login',
			]
		], $widgetOutput);

		// custom id and name attributes
		$widget->htmlOptions['name'] = 'alpha';
		$widget->htmlOptions['id'] = 'beta';
		$widgetOutput = $this->runAndCapture($widget);
		$this->assertTag([
			'tag' => 'input',
			'attributes' => [
				'foo' => 'bar',
				'name' => 'alpha',
				'id' => 'beta',
			]
		], $widgetOutput);
	}

	/**
	 * @covers ::run
	 */
	public function testScriptGeneration()
	{
		$widget = $this->makeWidget();
		$widget->name = 'foo';
		$widget->value = 'val';
		$widget->options = ['language' => 'ru', 'foo' => 'bar'];
		$widget->events = ['click' => 'return foobar()'];
		$this->runAndCapture($widget);

		$cs = Yii::app()->clientScript;
		$script = $cs->scripts[$cs->defaultScriptPosition][get_class($widget) . '#' . $widget->getId()];
		$this->assertEquals(
			"jQuery('#foo').datepicker({'language':'ru','foo':'bar'}).on('click','return foobar()');",
			$script
		);
	}

	public function registerAssetsProvider()
	{
		return [
			[true],
			[false],
		];
	}

	/**
	 * @param bool $minify
	 * @dataProvider registerAssetsProvider
	 * @covers ::registerAssets
	 */
	public function testRegisterAssets($minify)
	{
		$cs = Yii::app()->clientScript;
		$cs->reset();

		$widget = $this->makeWidget();
		$widget->minifiedAssets = $minify;
		$widget->name = 'foo';
		$widget->value = 'val';
		$widget->options = ['language' => 'ru'];
		$widget->init();

		// non-minifed assets
		$this->runAndCapture($widget);
		$assetPath = realpath(__DIR__ . '/../../assets');
		$assetUrl = Yii::app()->assetManager->publish($assetPath);
		$minifyPrefix = $minify ? '.min' : '';

		$this->assertTrue($cs->isCssFileRegistered($assetUrl . '/bootstrap-datepicker' . $minifyPrefix . '.css'));
		$this->assertTrue(
			$cs->isScriptFileRegistered(
				$assetUrl . '/bootstrap-datepicker' . $minifyPrefix . '.js',
				$cs->defaultScriptFilePosition
			)
		);
		$this->assertTrue(
			$cs->isScriptFileRegistered(
				$assetUrl . '/locales/bootstrap-datepicker.ru.js',
				$cs->defaultScriptFilePosition
			)
		);
	}
}