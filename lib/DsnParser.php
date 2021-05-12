<?php
namespace IrisHelpers;

class DsnParser {
	/** @var array<string, string> $key_translations */
	public $key_translations;

	/**
	 * Construct a DsnParser
	 *
	 * @param array<string, string> $key_translations
	 */
	public function __construct(array $key_translations = []) {
		$this->key_translations = $key_translations;
	}
	
	/**
	 * Construct a DsnParser with defaults for a database connection with
	 * Laravel's `\Illuminate\Database\Capsule\Manager`
	 *
	 * @return DsnParser
	 */
	public static function forLaravelDatabase(): self {
		return new self([
			'PROTOCOL' => 'driver',
			'VALUE' => 'database',
			'dbname' => 'database',
		]);
	}

	/**
	 * Construct a DsnParser with defaults for a database connection for
	 * the `cakephp/phinx` migration tool
	 *
	 * @return DsnParser
	 */
	public static function forPhinx(): self {
		return new self([
			'PROTOCOL' => 'adapter',
			'VALUE' => 'name',
			'dbname' => 'name',
			'username' => 'user',
			'password' => 'pass',
		]);
	}

	/**
	 * Parse a DSN
	 *
	 * @param string $dsn The DSN to parse
	 * @param bool $multi_entry Whether to allow multiple entries of the same key
	 * @return array<string, mixed> The parsed DSN values
	 */
	public function parse(string $dsn, bool $multi_entry = false): array {
		list($protocol, $params) = explode(":", $dsn, 2);
		$params = array_map('trim', explode(";", $params));

		// Parse out the key/value pairs
		$options = ['PROTOCOL' => $protocol];
		foreach ($params as $param) {
			if (strpos($param, '=') === false) {
				// Standalone value

				if ($multi_entry) {
					$options['VALUE'] = $options['VALUE'] ?? [];
					$options['VALUE'][] = $param;
				} else {
					$options['VALUE'] = $param;
				}
			} else {
				// Key/value pair

				list($key, $value) = array_map('trim', explode("=", $param, 2));
				if ($multi_entry) {
					$options[$key] = $options[$key] ?? [];
					$options[$key][] = $value;
				} else {
					$options[$key] = $value;
				}
			}
		}

		// Do key translations
		foreach ($options as $key => $value) {
			if (array_key_exists($key, $this->key_translations)) {
				$options[$this->key_translations[$key]] = $value;
				unset($options[$key]);
			}
		}

		return array_filter($options);
	}
}
