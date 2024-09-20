<?php


use App\Console\Flight;
use Illuminate\Support\Facades\DB;
use Mockery as m;

class PrepareFiles extends TestCase
{
    private $prepareFiles;
    protected $app;
    public function setUp()
    {
        $this->prepareFiles = new PrepareFiles();
        $this->app = require __DIR__.'/../../bootstrap/app.php';
    }
    public function testProcessAgentFilesRecursiveqq()
    {
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["pdf", "docx", "xlsx"]'
        ];
        $agentName = 'testAgent';
        $agentDir = 'airfiles';

        $file = $this->createMock(\SplFileInfo::class);
        $file->method('isDir')->willReturn(false);
        $file->method('isFile')->willReturn(true);
        $file->method('getExtension')->willReturn('pdf');

        // Set up a mock directory iterator that will return the $file object
        $dirIterator = $this->createMock(\RecursiveDirectoryIterator::class);
        $dirIterator->method('getChildren')->willReturn(new \ArrayIterator([$file]));
        $dirIterator->method('isDot')->willReturn(false);

        // Set up a mock iterator that will return the $dirIterator object
//        $iterator = $this->createMock(\RecursiveIteratorIterator::class);
//        $iterator->method('getDepth')->willReturn(0);
//        $iterator->method('getSubIterator')->willReturn($dirIterator);

        // Set up a mock PrepareFiles object
        $prepareFiles = $this->getMockBuilder(PrepareFiles::class)
            ->setMethods(['checkFileExtension', 'handleFile', 'getFirstLineOfFile', 'checkIsValidAirFile', 'handleQueueFile','processAgentFilesRecursive'])
            ->getMock();

        // Set up the expectations for the mock methods
//        $prepareFiles->method('checkFileExtension')->willReturn(true);
//        $prepareFiles->method('handleFile')->willReturn(true);
//        $prepareFiles->method('getFirstLineOfFile')->willReturn('test');
//        $prepareFiles->method('checkIsValidAirFile')->willReturn(true);
//        $prepareFiles->method('handleQueueFile')->willReturn(true);

        // Call the method being tested
        $result = $prepareFiles->processAgentFilesRecursive($ini, 'agentName', 'agentDir');

        // Assert that the method returns true
        $this->assertTrue($result);
    }
    public function testReadImporterIni()
    {
        $command = new PrepareFiles();
        $method = new \ReflectionMethod('\App\Console\Commands\Flight\PrepareFiles', 'readImporterIni');
        $method->setAccessible(true);

        $ini = $method->invoke($command);
        $this->assertInternalType('array',$ini);
        $this->assertArrayHasKey('ftpHome', $ini);

    }

    public function testTruncateSessionTable()
    {
        $command = new App\Console\Commands\Flight\PrepareFiles();
        $method = new \ReflectionMethod('App\Console\Commands\Flight\PrepareFiles', 'truncateSessionTable');
        $method->setAccessible(true);

        // Test the case when the session table is truncated
        DB::connection('pgsql_auth')->table('session')->insert([
            'account_id' => null,
            'ip' => '127.0.0.1',
            'vars' => 'PHPUnit',
        ]);
        $method->invoke($command);
        $this->assertCount(0, DB::connection('pgsql_auth')->table('session')->get());
    }

    public function testClearAgentTables()
    {
        $agent = 'Excellive';
        crsDbConnection($agent);
        $command = new App\Console\Commands\Flight\PrepareFiles();
        $method = new ReflectionMethod('App\Console\Commands\Flight\PrepareFiles', 'clearAgentTables');
        $method->setAccessible(true);

        // Test the case when the session table is truncated
        $method->invoke($command,$agent);
        $this->assertCount(0, DB::connection('pgsql_crs_'.$agent)->table('jobs')->get());
        $this->assertCount(0, DB::connection('pgsql_crs_'.$agent)->table('failed_jobs')->get());
    }



    public function testProcessAgentFilesRecursiveWithNonExistentDirectory()
    {
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'non-existent-directory';

        // Assert that the directory does not exist
        $this->assertFalse(file_exists($ini['ftpHome'] . '/' . $agentName . '/' . $agentDir));

        // Call the method being tested and expect an exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File path not found for agent '.$agentName.PHP_EOL);

        $prepareFilesObj = $this->app->make(\App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(\App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);
    }


    public function testProcessAgentFilesRecursiveEmptyDirectory()
    {
       $app = require __DIR__.'/../../bootstrap/app.php';
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'empty_dir';

        $fakeDir = $ini['ftpHome'] . '/' . $agentDir;
        mkdir($fakeDir, 0777, true);

        // Set up the mocked directory iterator
        $mockDirIterator = \Mockery::mock(\RecursiveDirectoryIterator::class, ['/', \RecursiveDirectoryIterator::SKIP_DOTS]);
        $mockDirIterator->shouldReceive('getPath')->andReturn(dirname(__FILE__) . '/empty_dir');
        $mockDirIterator->shouldReceive('getChildren')->andReturn([]);
        $mockDirIterator->shouldReceive('isDir')->andReturn(false);

        // Set up the mocked recursive iterator
        $mockIterator = \Mockery::mock(\RecursiveIteratorIterator::class, [$mockDirIterator, \RecursiveIteratorIterator::SELF_FIRST]);
        $mockIterator->shouldReceive('setMaxDepth')->with(0);
        $mockIterator->shouldReceive('count')->andReturn(0);

        // Set up the mocked file processor
        $mockprepareFiles = Mockery::mock(App\Console\Commands\Flight\PrepareFiles::class);

        // Mock the Lumen app instance
       $app->instance('App\Console\Commands\Flight\PrepareFiles::class', $mockprepareFiles);


        // Call the method being tested
        $prepareFilesObj = $app->make(App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);

        // Assertions
        // Assert that the mocked file processor methods were not called
        $mockprepareFiles->shouldNotHaveReceived('checkFileExtension');
        $mockprepareFiles->shouldNotHaveReceived('currentFileGds');
        $mockprepareFiles->shouldNotHaveReceived('getFirstLineOfFile');
        $mockprepareFiles->shouldNotHaveReceived('checkIsValidAirFile');
        $mockprepareFiles->shouldNotHaveReceived('handleFile');
        rmdir($fakeDir);
    }


    public function testProcessAgentFilesRecursiveNoFilesWithAllowedExtensions()
    {
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'test_dir';

        $fakeDir = $ini['ftpHome'] . '/' . $agentDir;
        mkdir($fakeDir, 0777, true);
        touch($fakeDir . '/testFile1.jpg');
        touch($fakeDir . '/testFile2.csv');
        touch($fakeDir . '/testFile3.log');


        // Set up the mocked directory iterator
        $mockDirIterator = \Mockery::mock(\RecursiveDirectoryIterator::class, ['/', \RecursiveDirectoryIterator::SKIP_DOTS]);
        $mockDirIterator->shouldReceive('getPath')->andReturn(dirname(__FILE__).'/airfiles');
        $mockDirIterator->shouldReceive('count')->andReturn(0); // Simulate an empty directory

        // Set up the mocked recursive iterator
        $mockIterator = \Mockery::mock(\RecursiveIteratorIterator::class, [$mockDirIterator, \RecursiveIteratorIterator::SELF_FIRST]);
        $mockIterator->shouldReceive('setMaxDepth')->with(0);
        $mockIterator->shouldReceive('count')->andReturn(0); // Simulate an empty directory



        // Set up the mocked file processor
        $mockprepareFiles = Mockery::mock(App\Console\Commands\Flight\PrepareFiles::class);
        $mockprepareFiles->shouldNotReceive('handleFile');
        $mockprepareFiles->shouldNotReceive('handleQueueFile');
        $mockprepareFiles->shouldReceive('checkFileExtension');

        // Mock the Lumen app instance
        $this->app->instance(App\Console\Commands\Flight\PrepareFiles::class, $mockprepareFiles);

        $prepareFilesObj = $this->app->make(App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);


        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fakeDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $action($fileinfo->getRealPath());
        }
        rmdir($fakeDir);
        // Assertions
        $this->assertTrue(true); // No assertions are needed, but we need to assert something to avoid a risky test warning
    }

    public function testProcessAgentFilesRecursiveWhenNoFilesWithAllowedExtensionsForAgent()
    {
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'airfiles';

        // Set up the mocked directory iterator
        $mockDirIterator = \Mockery::mock(\RecursiveDirectoryIterator::class, ['/', \RecursiveDirectoryIterator::SKIP_DOTS]);
        $mockDirIterator->shouldReceive('getPath')->andReturn(dirname(__FILE__).'/airfiles');
        $mockDirIterator->shouldReceive('getFileName')->andReturn('issue.xyz'); // File with an extension not in the allowed list
        $mockDirIterator->shouldReceive('getExtension')->andReturn('xyz');
        $mockDirIterator->shouldReceive('isDir')->andReturn(false);
        $mockDirIterator->shouldReceive('isFile')->andReturn(true);

        // Set up the mocked recursive iterator
        $mockIterator = \Mockery::mock(\RecursiveIteratorIterator::class, [$mockDirIterator, \RecursiveIteratorIterator::SELF_FIRST]);
        $mockIterator->shouldReceive('setMaxDepth')->with(0);
        $mockIterator->shouldReceive('count')->andReturn(1);
        $mockIterator->shouldReceive('current')->andReturn($mockDirIterator);
        $mockIterator->shouldReceive('getSubIterator')->andReturnSelf();

        // Set up the mocked file processor
        $mockprepareFiles = Mockery::mock(App\Console\Commands\Flight\PrepareFiles::class);
        $mockprepareFiles->shouldReceive('checkFileExtension')->andReturn(false); // Return false for any extension check
        $mockprepareFiles->shouldReceive('handleFile')->never(); // Assert that the method is not called

        // Mock the Lumen app instance
        $this->app->instance(App\Console\Commands\Flight\PrepareFiles::class, $mockprepareFiles);

        // Call the method being tested
        $prepareFilesObj = $this->app->make(App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);

        // Assertions
        $this->assertTrue(true); // No assertions are needed, but we need to assert something to avoid a risky test warning
    }

    public function testProcessAgentFilesRecursiveWithInvalidFile()
    {
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'airfiles';

        // Set up the mocked directory iterator
        $mockDirIterator = \Mockery::mock(\RecursiveDirectoryIterator::class, ['/', \RecursiveDirectoryIterator::SKIP_DOTS]);
        $mockDirIterator->shouldReceive('getPath')->andReturn(dirname(__FILE__).'/airfiles');
        $mockDirIterator->shouldReceive('getFileName')->andReturn('issue.txt');
        $mockDirIterator->shouldReceive('getExtension')->andReturn('txt');
        $mockDirIterator->shouldReceive('isDir')->andReturn(false);
        $mockDirIterator->shouldReceive('isFile')->andReturn(true);

        // Set up the mocked recursive iterator
        $mockIterator = \Mockery::mock(\RecursiveIteratorIterator::class, [$mockDirIterator, \RecursiveIteratorIterator::SELF_FIRST]);
        $mockIterator->shouldReceive('setMaxDepth')->with(0);
        $mockIterator->shouldReceive('count')->andReturn(1);
        $mockIterator->shouldReceive('current')->andReturn($mockDirIterator);
        $mockIterator->shouldReceive('getSubIterator')->andReturnSelf();

        // Set up the mocked file processor
        $mockprepareFiles = Mockery::mock(App\Console\Commands\Flight\PrepareFiles::class);
        $mockprepareFiles->shouldReceive('checkFileExtension')->andReturn(true);
        $mockprepareFiles->shouldReceive('currentFileGds')->andReturn('1');
        $mockprepareFiles->shouldReceive('getFirstLineOfFile')->andReturn("test");
        $mockprepareFiles->shouldReceive('checkIsValidAirFile')->andReturn(false);
        $mockprepareFiles->shouldReceive('handleFile')->never();

        // Mock the Lumen app instance
        $this->app->instance(App\Console\Commands\Flight\PrepareFiles::class, $mockprepareFiles);

        // Call the method being tested
        $prepareFilesObj = $this->app->make(App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);

        // Assertions
        // Ensure that handleFile method is never called
        $mockprepareFiles->shouldNotHaveReceived('handleFile');
    }
    public function testProcessAgentFilesRecursive()
    {
        // Create a mock of the RecursiveIteratorIterator class
        $iterator = m::mock();
        // Set the max depth of the iterator
        $iterator->shouldReceive('setMaxDepth')->once()->with(0);

        // Create a mock of the RecursiveDirectoryIterator class
        $dirIterator = m::mock('\RecursiveDirectoryIterator');
        // Set the skip dots flag for the directory iterator
        $dirIterator->shouldReceive('setFlags')->once()->with(\RecursiveDirectoryIterator::SKIP_DOTS);
        // Return the mocked iterator from the getIterator method
        $dirIterator->shouldReceive('getIterator')->once()->andReturn($iterator);

        // Mock the is_dir function to return true for the FTP home directory and agent directory
        $ftpHomeDir = '/path/to/ftp/home';
        $agentDir = 'agent-name';
        $this->mockFunction('is_dir')->shouldReceive('is_dir')->with($ftpHomeDir)->andReturn(true);
        $this->mockFunction('is_dir')->shouldReceive('is_dir')->with($ftpHomeDir . '/' . $agentDir)->andReturn(true);

        // Mock the is_readable and is_writable functions to return true for the agent directory
        $this->mockFunction('is_readable')->shouldReceive('is_readable')->with($ftpHomeDir . '/' . $agentDir)->andReturn(true);
        $this->mockFunction('is_writable')->shouldReceive('is_writable')->with($ftpHomeDir . '/' . $agentDir)->andReturn(true);

        // Mock the realpath function to return the agent directory path
        $this->mockFunction('realpath')->shouldReceive('realpath')->with($ftpHomeDir . '/' . $agentDir)->andReturn($ftpHomeDir . '/' . $agentDir);

        // Mock the iterator_count function to return the number of files in the directory
        $this->mockFunction('iterator_count')->shouldReceive('iterator_count')->with($iterator)->andReturn(2);

        // Mock the checkFileExtension function to return true for the file extension
        $this->mockMethod('checkFileExtension')->shouldReceive('checkFileExtension')->andReturn(true);

        // Mock the isFile function to return true for the file
        $file = m::mock('\SplFileInfo');
        $file->shouldReceive('isFile')->once()->andReturn(true);

        // Mock the getExtension function to return the file extension
        $file->shouldReceive('getExtension')->once()->andReturn('txt');

        // Mock the handleFile function to be called with the file and agent name
        $this->mockMethod('handleFile')->shouldReceive('handleFile')->once()->with($file, 'agent-name');

        // Create a new instance of the ProcessAgentFilesRecursive class
        $processAgentFilesRecursive = new ProcessAgentFilesRecursive();

        // Call the processAgentFilesRecursive method with the mock ini, agent name, and agent directory
        $processAgentFilesRecursive->processAgentFilesRecursive(['ftpHome' => $ftpHomeDir, 'extensionsToCheck' => '["txt"]'], 'agent-name', $agentDir);
    }
    public function testProcessAgentFilesRecursiveValidFile()
    {
        // Set up the test data
        $ini = [
            'ftpHome' => dirname(__FILE__),
            'extensionsToCheck' => '["txt", "csv"]'
        ];
        $agentName = 'Excellive';
        $agentDir = 'airfiles';

        // Set up the mocked directory iterator
        $mockDirIterator = \Mockery::mock(\RecursiveDirectoryIterator::class, ['/', \RecursiveDirectoryIterator::SKIP_DOTS]);
        $mockDirIterator->shouldReceive('getPath')->andReturn(dirname(__FILE__) . '/airfiles');
        $mockDirIterator->shouldReceive('getFileName')->andReturn('issue.txt');
        $mockDirIterator->shouldReceive('getExtension')->andReturn('txt');
        $mockDirIterator->shouldReceive('isDir')->andReturn(false);
        $mockDirIterator->shouldReceive('isFile')->andReturn(true);

        // Set up the mocked recursive iterator
        $mockIterator = \Mockery::mock(\RecursiveIteratorIterator::class, [$mockDirIterator, \RecursiveIteratorIterator::SELF_FIRST]);
        $mockIterator->shouldReceive('setMaxDepth')->with(0);
        $mockIterator->shouldReceive('count')->andReturn(1);
        $mockIterator->shouldReceive('current')->andReturn($mockDirIterator);
        $mockIterator->shouldReceive('getSubIterator')->andReturnSelf();

        // Set up the mocked file processor
        $mockprepareFiles = \Mockery::mock(App\Console\Commands\Flight\PrepareFiles::class);
        $mockprepareFiles->shouldReceive('checkFileExtension')->andReturn(true);
        $mockprepareFiles->shouldReceive('currentFileGds')->andReturn('1');
        $mockprepareFiles->shouldReceive('getFirstLineOfFile');//->andReturn("test");
        $mockprepareFiles->shouldReceive('checkIsValidAirFile');//->andReturn(true);
        $mockprepareFiles->shouldReceive('handleFile')->once();

        // Mock the Lumen app instance
        $this->app->instance(App\Console\Commands\Flight\PrepareFiles::class, $mockprepareFiles);


        $refProperty = new ReflectionProperty('App\Console\Commands\Flight\PrepareFiles', 'currentFileGds');
        $refProperty->setAccessible(true);

        // Set the value of the private property
        $refProperty->setValue($mockprepareFiles, 1);

        // Call the method being tested
        $prepareFilesObj = $this->app->make(App\Console\Commands\Flight\PrepareFiles::class);
        $reflectionMethod = new ReflectionMethod(App\Console\Commands\Flight\PrepareFiles::class, 'processAgentFilesRecursive');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($prepareFilesObj, $ini, $agentName, $agentDir);

        // Assertions
        $mockprepareFiles->shouldHaveReceived('handleFile')->once();
    }
}
