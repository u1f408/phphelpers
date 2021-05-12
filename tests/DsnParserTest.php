<?php
use IrisHelpers\DsnParser;
use PHPUnit\Framework\TestCase;

final class DsnParserTest extends TestCase {
	public function test_multi_entry_parses_array() {
		$parsed = (new DsnParser())->parse('protocol:0;1;key=value1;key=value2', true);
		$this->assertEquals($parsed['PROTOCOL'], 'protocol');
		$this->assertEquals($parsed['VALUE'], ['0', '1']);
		$this->assertEquals($parsed['key'], ['value1', 'value2']);
	}

	public function test_non_multi_entry_uses_last_value() {
		$parsed = (new DsnParser())->parse('protocol:0;1;key=value1;key=value2');
		$this->assertEquals($parsed['PROTOCOL'], 'protocol');
		$this->assertEquals($parsed['VALUE'], '1');
		$this->assertEquals($parsed['key'], 'value2');
	}

	public function test_sqlite_memory() {
		$parsed = (new DsnParser())->parse('sqlite::memory:');
		$this->assertEquals($parsed['PROTOCOL'], 'sqlite');
		$this->assertEquals($parsed['VALUE'], ':memory:');
	}

	public function test_sqlite_relative_path() {
		$parsed = (new DsnParser())->parse('sqlite:database.sqlite3');
		$this->assertEquals($parsed['PROTOCOL'], 'sqlite');
		$this->assertEquals($parsed['VALUE'], 'database.sqlite3');
	}

	public function test_sqlite_absolute_path() {
		$parsed = (new DsnParser())->parse('sqlite:/path/to/database.sqlite3');
		$this->assertEquals($parsed['PROTOCOL'], 'sqlite');
		$this->assertEquals($parsed['VALUE'], '/path/to/database.sqlite3');
	}

	public function test_pgsql_dbname() {
		$parsed = (new DsnParser())->parse('pgsql:dbname=test');
		$this->assertEquals($parsed['PROTOCOL'], 'pgsql');
		$this->assertEquals($parsed['dbname'], 'test');
	}

	public function test_pgsql_host_port_dbname() {
		$parsed = (new DsnParser())->parse('pgsql:host=localhost;port=5432;dbname=test');
		$this->assertEquals($parsed['PROTOCOL'], 'pgsql');
		$this->assertEquals($parsed['host'], 'localhost');
		$this->assertEquals($parsed['port'], '5432');
		$this->assertEquals($parsed['dbname'], 'test');
	}
	
	public function test_laravel_sqlite_memory() {
		$parsed = DsnParser::forLaravelDatabase()->parse('sqlite::memory:');
		$this->assertEquals($parsed['driver'], 'sqlite');
		$this->assertEquals($parsed['database'], ':memory:');
	}

	public function test_laravel_pgsql_host_port_dbname() {
		$parsed = DsnParser::forLaravelDatabase()->parse('pgsql:host=localhost;port=5432;dbname=test');
		$this->assertEquals($parsed['driver'], 'pgsql');
		$this->assertEquals($parsed['host'], 'localhost');
		$this->assertEquals($parsed['port'], '5432');
		$this->assertEquals($parsed['database'], 'test');
	}
	
	public function test_phinx_sqlite_memory() {
		$parsed = DsnParser::forPhinx()->parse('sqlite::memory:');
		$this->assertEquals($parsed['adapter'], 'sqlite');
		$this->assertEquals($parsed['name'], ':memory:');
	}

	public function test_phinx_pgsql_host_port_dbname_username_password() {
		$parsed = DsnParser::forPhinx()->parse('pgsql:host=localhost;port=5432;dbname=test;username=user;password=pass');
		$this->assertEquals($parsed['adapter'], 'pgsql');
		$this->assertEquals($parsed['host'], 'localhost');
		$this->assertEquals($parsed['port'], '5432');
		$this->assertEquals($parsed['name'], 'test');
		$this->assertEquals($parsed['user'], 'user');
		$this->assertEquals($parsed['pass'], 'pass');
	}
}
