<?php

namespace JardisCore\DotEnv\Tests\unit\Reader;

use JardisCore\DotEnv\Casting\CastTypeHandler;
use JardisCore\DotEnv\Reader\LoadValuesFromFiles;
use PHPUnit\Framework\TestCase;

class LoadValuesFromFilesTest extends TestCase
{
    private CastTypeHandler $CastTypeHandler;
    private LoadValuesFromFiles $loadValuesFromFiles;

    protected function setUp(): void
    {
        $this->CastTypeHandler = $this->createMock(CastTypeHandler::class);
        $this->loadValuesFromFiles = new LoadValuesFromFiles($this->CastTypeHandler);
    }

    public function testReturnsMergedValuesNotPublic()
    {
        $this->CastTypeHandler
            ->method('__invoke')
            ->willReturnArgument(0); // Rückgabe des gleichen Werts für einfache Tests

        $file = [dirname(__DIR__) . '/../fixtures/.env'];

        $result = ($this->loadValuesFromFiles)($file, false);

        $expected = [
            'DB_HOST' => 'prodHost',
            'DB_NAME' => 'prodName',
            'HOME' => '~',
            'DATABASE_URL' => 'mysql://${DB_HOST}:${DB_NAME}@localhost',
            'BOOL_VAR' => 'true',
            'INT_VAR' => '1',
            'TEST' => '[a=>1,2,b=>true,4,5,6,7,test=>[1,2,3,4]]'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvokeSkipsUnreadableFiles()
    {
        $this->CastTypeHandler
            ->method('__invoke')
            ->willReturnArgument(0); // Rückgabe des gleichen Werts für einfache Tests

        $file = [dirname(__DIR__) . '/../fixtures/.notFoundenv'];

        $result = ($this->loadValuesFromFiles)($file, false);

        $this->assertEquals([], $result);
    }

    public function testLoadFileValuesParsesValidRowsPublicMode()
    {
        $this->CastTypeHandler
            ->method('__invoke')
            ->willReturnCallback(function ($value) {
                return strtoupper($value);
            });

        $file = [dirname(__DIR__) . '/../fixtures/.env'];

        $result = ($this->loadValuesFromFiles)($file);

        $this->assertEquals([], $result);
        $this->assertEquals('PRODHOST', getenv('DB_HOST'));
        $this->assertEquals('PRODNAME', getenv('DB_NAME'));
    }
}
