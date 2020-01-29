<?php
/**
 * Tests KO7 i18n class
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.i18n
 *
 * @package    KO7
 * @category   Tests
 *
 * , Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) 2008´- 2016 Kohana Team
 * @copyright  (c) since  2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_I18nTest extends Unittest_TestCase {

	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	protected $environmentDefault =	[
		'I18n::$lang' => 'en-us'
	];

	/**
	 * Provides test data for test_lang()
	 *
	 * @return array
	 */
	public function provider_lang() : array
	{
		return [
			// $input, $expected_result
			[
				NULL, 'en-us'
			],
			[
				'es-es', 'es-es'
			],
		];
	}

	/**
	 * Tests I18n::lang()
	 *
	 * @dataProvider provider_lang
	 *
	 * @param  boolean  $input     Input for I18n::lang
	 * @param  boolean  $expected  Output for I18n::lang
	 */
	public function test_lang($input, $expected)
	{
		self::assertSame($expected, I18n::lang($input));
		self::assertSame($expected, I18n::lang());
	}

	/**
	 * Provides test data for test_get()
	 *
	 * @return array
	 */
	public function provider_get() : array
	{
		return [
			// $lang, $input, $expected
			['en-us', 'Hello, world!', 'Hello, world!'],
			['es-es', 'Hello, world!', '¡Hola, mundo!'],
			['fr-fr', 'Hello, world!', 'Bonjour, monde!'],
		];
	}

	/**
	 * Tests i18n::get()
	 *
	 * @dataProvider provider_get
	 *
	 * @param string $lang      Target Language to translate to
	 * @param string $input     Input Translation String
	 * @param string $expected  Expected Result
	 */
	public function test_get(string $lang, string $input, string $expected)
	{
		// Set Language
		I18n::lang($lang);

		// Test I18n::get function
		self::assertSame($expected, I18n::get($input));

		// Test I18n::get function with source language same as target language (we don't expect to be translated)
		self::assertSame($input, I18n::get($input, $lang, $lang));

		// Test __() function
		self::assertSame($expected, __($input));

		// Test __() function with source language (Note: we don't expect the string to be translated)
		self::assertSame($input, __($input, NULL, $lang));

		// Test I18n::get function with target language passed as variable
		I18n::lang('en-us');
		self::assertSame($expected, I18n::get($input, $lang));
	}

	/**
	 * @return array
	 */
	public function provider_get_values() :array
	{
		return [
			// $lang, $input, $values, $expected
			['en-us', 'Good Morning :name!', [':name' => 'Koseven'], 'Good Morning Koseven!'],
			['es-es', 'Good Morning :name!', [':name' => 'Koseven'], 'Buenos dias Koseven!'],
			['fr-fr', 'Good Morning :name!', [':name' => 'Koseven'], 'Bonjour Koseven!'],
		];
	}

	/**
	 * Tests i18n::get with values passed to replace
	 *
	 * @dataProvider provider_get_values
	 *
	 * @param string $lang      Target Language to translate to
	 * @param string $input     Input Translation String
	 * @param array  $values    Values to replace in translated string
	 * @param string $expected  Expected Result
	 */
	public function test_get_values(string $lang, string $input, array $values, string $expected)
	{
		// Set Language
		I18n::lang($lang);

		// Test I18n::get function with values to replace
		self::assertSame($expected, I18n::get([$input, $values]));

		// Test __() function with values to replace
		self::assertSame($expected, __($input, $values));

		// Don't expect string to be translated but expect values to be re-placed
		self::assertSame('Good Morning Koseven!', I18n::get([$input, $values], $lang, $lang));
	}

}
