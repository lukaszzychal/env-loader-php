<?php

declare(strict_types=1);

namespace LukaszZychal\EnvLoader\Tests;

use LukaszZychal\EnvLoader\EnvLoader;
use PHPUnit\Framework\TestCase;

class EnvLoaderTest extends TestCase
{
    private string $testEnvFile;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->testEnvFile = sys_get_temp_dir() . '/test.env';
        $this->originalEnv = $_ENV;

        // Clear $_ENV for clean testing
        $_ENV = [];
    }

    protected function tearDown(): void
    {
        // Clean up test file
        if (file_exists($this->testEnvFile)) {
            unlink($this->testEnvFile);
        }

        // Restore original environment
        $_ENV = $this->originalEnv;

        // Clean up any test environment variables
        putenv('TEST_VAR');
        putenv('TEST_VAR_2');
        putenv('TEST_VAR_3');
        putenv('QUOTED_VAR');
        putenv('SINGLE_QUOTED_VAR');
        putenv('COMMENTED_VAR');
        putenv('EMPTY_VALUE');
    }

    public function testLoadWithNonExistentFile(): void
    {
        $result = EnvLoader::load('/non/existent/file.env');
        $this->assertFalse($result);
    }

    public function testLoadWithValidEnvFile(): void
    {
        $envContent = "TEST_VAR=test_value\nTEST_VAR_2=another_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::load($this->testEnvFile);
        $this->assertTrue($result);

        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
        $this->assertEquals('another_value', EnvLoader::get('TEST_VAR_2'));
    }

    public function testLoadWithQuotedValues(): void
    {
        $envContent = "QUOTED_VAR=\"quoted value\"\nSINGLE_QUOTED_VAR='single quoted'\n";
        file_put_contents($this->testEnvFile, $envContent);

        EnvLoader::load($this->testEnvFile);

        $this->assertEquals('quoted value', EnvLoader::get('QUOTED_VAR'));
        $this->assertEquals('single quoted', EnvLoader::get('SINGLE_QUOTED_VAR'));
    }

    public function testLoadSkipsComments(): void
    {
        $envContent = "# This is a comment\nTEST_VAR=test_value\n# Another comment\nTEST_VAR_2=another_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        EnvLoader::load($this->testEnvFile);

        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
        $this->assertEquals('another_value', EnvLoader::get('TEST_VAR_2'));
        $this->assertFalse(EnvLoader::has('COMMENTED_VAR'));
    }

    public function testLoadSkipsEmptyLines(): void
    {
        $envContent = "\n\nTEST_VAR=test_value\n\n\nTEST_VAR_2=another_value\n\n";
        file_put_contents($this->testEnvFile, $envContent);

        EnvLoader::load($this->testEnvFile);

        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
        $this->assertEquals('another_value', EnvLoader::get('TEST_VAR_2'));
    }

    public function testLoadWithInvalidLines(): void
    {
        $envContent = "INVALID_LINE_WITHOUT_EQUALS\nTEST_VAR=test_value\nANOTHER_INVALID_LINE\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::load($this->testEnvFile);
        $this->assertTrue($result);

        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
        $this->assertFalse(EnvLoader::has('INVALID_LINE_WITHOUT_EQUALS'));
        $this->assertFalse(EnvLoader::has('ANOTHER_INVALID_LINE'));
    }

    public function testLoadWithEmptyValue(): void
    {
        $envContent = "EMPTY_VALUE=\nTEST_VAR=test_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        EnvLoader::load($this->testEnvFile);

        $this->assertEquals('', EnvLoader::get('EMPTY_VALUE'));
        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
    }

    public function testLoadDoesNotOverwriteExistingEnvVars(): void
    {
        // Set an existing environment variable
        $_ENV['TEST_VAR'] = 'existing_value';
        putenv('TEST_VAR=existing_value');

        $envContent = "TEST_VAR=new_value\nTEST_VAR_2=another_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        EnvLoader::load($this->testEnvFile);

        // Should not overwrite existing variable
        $this->assertEquals('existing_value', EnvLoader::get('TEST_VAR'));
        // But should load new variables
        $this->assertEquals('another_value', EnvLoader::get('TEST_VAR_2'));
    }

    public function testGetWithDefaultValue(): void
    {
        $this->assertEquals('default_value', EnvLoader::get('NON_EXISTENT_VAR', 'default_value'));
        $this->assertNull(EnvLoader::get('ANOTHER_NON_EXISTENT_VAR'));
    }

    public function testHasReturnsTrueForExistingVariable(): void
    {
        $_ENV['TEST_VAR'] = 'test_value';

        $this->assertTrue(EnvLoader::has('TEST_VAR'));
        $this->assertFalse(EnvLoader::has('NON_EXISTENT_VAR'));
    }

    public function testHasReturnsTrueForSystemEnvironmentVariable(): void
    {
        putenv('SYSTEM_TEST_VAR=system_value');

        $this->assertTrue(EnvLoader::has('SYSTEM_TEST_VAR'));

        // Clean up
        putenv('SYSTEM_TEST_VAR');
    }

    public function testLoadAndReturnWithNonExistentFile(): void
    {
        $result = EnvLoader::loadAndReturn('/non/existent/file.env');
        $this->assertEquals([], $result);
    }

    public function testLoadAndReturnWithValidEnvFile(): void
    {
        $envContent = "TEST_VAR=test_value\nTEST_VAR_2=another_value\n# Comment line\nTEST_VAR_3=third_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::loadAndReturn($this->testEnvFile);

        $expected = [
            'TEST_VAR' => 'test_value',
            'TEST_VAR_2' => 'another_value',
            'TEST_VAR_3' => 'third_value'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadAndReturnWithQuotedValues(): void
    {
        $envContent = "QUOTED_VAR=\"quoted value\"\nSINGLE_QUOTED_VAR='single quoted'\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::loadAndReturn($this->testEnvFile);

        $expected = [
            'QUOTED_VAR' => 'quoted value',
            'SINGLE_QUOTED_VAR' => 'single quoted'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadAndReturnSkipsCommentsAndEmptyLines(): void
    {
        $envContent = "\n# This is a comment\nTEST_VAR=test_value\n\n# Another comment\n\nTEST_VAR_2=another_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::loadAndReturn($this->testEnvFile);

        $expected = [
            'TEST_VAR' => 'test_value',
            'TEST_VAR_2' => 'another_value'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadAndReturnWithEmptyValue(): void
    {
        $envContent = "EMPTY_VALUE=\nTEST_VAR=test_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::loadAndReturn($this->testEnvFile);

        $expected = [
            'EMPTY_VALUE' => '',
            'TEST_VAR' => 'test_value'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadAndReturnWithInvalidLines(): void
    {
        $envContent = "INVALID_LINE_WITHOUT_EQUALS\nTEST_VAR=test_value\nANOTHER_INVALID_LINE\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::loadAndReturn($this->testEnvFile);

        $expected = [
            'TEST_VAR' => 'test_value'
        ];

        $this->assertEquals($expected, $result);
    }
}
