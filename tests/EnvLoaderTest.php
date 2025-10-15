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

        // Clean up any local override files that might have been created
        $localFiles = [
            sys_get_temp_dir() . '/test.env.local',
            sys_get_temp_dir() . '/test.env.dev.local',
            sys_get_temp_dir() . '/test.env.prod.local',
            sys_get_temp_dir() . '/test.env.staging.local',
        ];

        foreach ($localFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
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
        putenv('BASE_VAR');
        putenv('DEV_VAR');
        putenv('LOCAL_VAR');
        putenv('SHARED_VAR');
        putenv('PRIORITY_VAR');
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

        EnvLoader::load($this->testEnvFile, null, false);

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

    public function testLoadWithEnvironmentSpecificFile(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $devEnvFile = sys_get_temp_dir() . '/test.env.dev';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Environment-specific .env.dev file
        file_put_contents($devEnvFile, "DEV_VAR=dev_value\nSHARED_VAR=dev_shared\n");

        EnvLoader::load($baseEnvFile, 'dev');

        // Should load from both files, with dev file overriding shared values
        $this->assertEquals('base_value', EnvLoader::get('BASE_VAR'));
        $this->assertEquals('dev_value', EnvLoader::get('DEV_VAR'));
        $this->assertEquals('dev_shared', EnvLoader::get('SHARED_VAR'));

        // Clean up
        unlink($baseEnvFile);
        unlink($devEnvFile);
    }

    public function testLoadWithLocalOverrideFile(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $localEnvFile = sys_get_temp_dir() . '/test.env.local';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Local override .env.local file
        file_put_contents($localEnvFile, "LOCAL_VAR=local_value\nSHARED_VAR=local_shared\n");

        EnvLoader::load($baseEnvFile, null, true);

        // Should load from both files, with local file overriding shared values
        $this->assertEquals('base_value', EnvLoader::get('BASE_VAR'));
        $this->assertEquals('local_value', EnvLoader::get('LOCAL_VAR'));
        $this->assertEquals('local_shared', EnvLoader::get('SHARED_VAR'));

        // Clean up
        unlink($baseEnvFile);
        unlink($localEnvFile);
    }

    public function testLoadWithEnvironmentAndLocalFiles(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $devEnvFile = sys_get_temp_dir() . '/test.env.dev';
        $devLocalEnvFile = sys_get_temp_dir() . '/test.env.dev.local';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Environment-specific .env.dev file
        file_put_contents($devEnvFile, "DEV_VAR=dev_value\nSHARED_VAR=dev_shared\n");

        // Local environment override .env.dev.local file
        file_put_contents($devLocalEnvFile, "LOCAL_VAR=local_value\nSHARED_VAR=local_shared\n");

        EnvLoader::load($baseEnvFile, 'dev', true);

        // Should load from all files, with local dev file having highest priority
        $this->assertEquals('base_value', EnvLoader::get('BASE_VAR'));
        $this->assertEquals('dev_value', EnvLoader::get('DEV_VAR'));
        $this->assertEquals('local_value', EnvLoader::get('LOCAL_VAR'));
        $this->assertEquals('local_shared', EnvLoader::get('SHARED_VAR'));

        // Clean up
        unlink($baseEnvFile);
        unlink($devEnvFile);
        unlink($devLocalEnvFile);
    }

    public function testLoadWithoutLocalOverrides(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $localEnvFile = sys_get_temp_dir() . '/test.env.local';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Local override .env.local file
        file_put_contents($localEnvFile, "LOCAL_VAR=local_value\nSHARED_VAR=local_shared\n");

        EnvLoader::load($baseEnvFile, null, false);

        // Should only load from base file, ignoring local overrides
        $this->assertEquals('base_value', EnvLoader::get('BASE_VAR'));
        $this->assertEquals('base_shared', EnvLoader::get('SHARED_VAR'));
        $this->assertFalse(EnvLoader::has('LOCAL_VAR'));

        // Clean up
        unlink($baseEnvFile);
        unlink($localEnvFile);
    }

    public function testLoadAndReturnWithEnvironmentSpecificFile(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $devEnvFile = sys_get_temp_dir() . '/test.env.dev';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Environment-specific .env.dev file
        file_put_contents($devEnvFile, "DEV_VAR=dev_value\nSHARED_VAR=dev_shared\n");

        $result = EnvLoader::loadAndReturn($baseEnvFile, 'dev');

        $expected = [
            'BASE_VAR' => 'base_value',
            'SHARED_VAR' => 'dev_shared',
            'DEV_VAR' => 'dev_value'
        ];

        $this->assertEquals($expected, $result);

        // Clean up
        unlink($baseEnvFile);
        unlink($devEnvFile);
    }

    public function testLoadAndReturnWithLocalOverrideFile(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $localEnvFile = sys_get_temp_dir() . '/test.env.local';

        // Base .env file
        file_put_contents($baseEnvFile, "BASE_VAR=base_value\nSHARED_VAR=base_shared\n");

        // Local override .env.local file
        file_put_contents($localEnvFile, "LOCAL_VAR=local_value\nSHARED_VAR=local_shared\n");

        $result = EnvLoader::loadAndReturn($baseEnvFile, null, true);

        $expected = [
            'BASE_VAR' => 'base_value',
            'SHARED_VAR' => 'local_shared',
            'LOCAL_VAR' => 'local_value'
        ];

        $this->assertEquals($expected, $result);

        // Clean up
        unlink($baseEnvFile);
        unlink($localEnvFile);
    }

    public function testLoadWithNonExistentEnvironmentFile(): void
    {
        $envContent = "TEST_VAR=test_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::load($this->testEnvFile, 'nonexistent');

        $this->assertTrue($result);
        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
    }

    public function testLoadWithNonExistentLocalFile(): void
    {
        $envContent = "TEST_VAR=test_value\n";
        file_put_contents($this->testEnvFile, $envContent);

        $result = EnvLoader::load($this->testEnvFile, null, true);

        $this->assertTrue($result);
        $this->assertEquals('test_value', EnvLoader::get('TEST_VAR'));
    }

    public function testFileLoadingOrder(): void
    {
        $baseEnvFile = sys_get_temp_dir() . '/test.env';
        $devEnvFile = sys_get_temp_dir() . '/test.env.dev';
        $devLocalEnvFile = sys_get_temp_dir() . '/test.env.dev.local';

        // All files have the same variable with different values
        file_put_contents($baseEnvFile, "PRIORITY_VAR=base\n");
        file_put_contents($devEnvFile, "PRIORITY_VAR=dev\n");
        file_put_contents($devLocalEnvFile, "PRIORITY_VAR=dev_local\n");

        EnvLoader::load($baseEnvFile, 'dev', true);

        // The last loaded file should win (dev_local)
        $this->assertEquals('dev_local', EnvLoader::get('PRIORITY_VAR'));

        // Clean up
        unlink($baseEnvFile);
        unlink($devEnvFile);
        unlink($devLocalEnvFile);
    }
}
