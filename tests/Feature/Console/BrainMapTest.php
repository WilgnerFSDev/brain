<?php

declare(strict_types=1);

use Brain\Console\BrainMap;

beforeEach(function () {
    $this->object = new BrainMap;
    $this->reflection = new ReflectionClass(get_class($this->object));
});

describe('load testsuite', function () {
    beforeEach(function () {
        config()->set('brain.root', __DIR__.'/../Fixtures/Brain');
    });

    it('should return a list of the entire set of domains', function () {
        $brain = new BrainMap;
        $brain->load();

        $data = [
            'Example' => [
                'domain' => 'Example',
                'path' => '/Users/r2luna/code/0..play/brain/tests/Feature/Console/../Fixtures/Brain/Example',
                'processes' => [
                    [
                        'name' => 'ExampleProcess',
                        'chain' => false,
                        'tasks' => [
                            [
                                'name' => 'ExampleTask4',
                                'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4",
                                'queue' => true,
                                'properties' => [],
                            ],
                        ],
                    ],
                ],
                'tasks' => [
                    [
                        'name' => 'ExampleTask',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask",
                        'queue' => false,
                        'properties' => [
                            ['name' => 'email', 'type' => 'string', 'direction' => 'output'],
                            ['name' => 'paymentId', 'type' => 'int', 'direction' => 'output'],
                        ],
                    ],
                    [
                        'name' => 'ExampleTask2',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask2",
                        'queue' => false,
                        'properties' => [
                            ['name' => 'email', 'type' => 'string', 'direction' => 'output'],
                            ['name' => 'paymentId', 'type' => 'int', 'direction' => 'output'],
                            ['name' => 'id', 'type' => 'int', 'direction' => 'input'],
                        ],
                    ],
                    [
                        'name' => 'ExampleTask3',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask3",
                        'queue' => false,
                        'properties' => [],
                    ],
                    [
                        'name' => 'ExampleTask4',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4",
                        'queue' => true,
                        'properties' => [],
                    ],
                ],
                'queries' => [
                    [
                        'name' => 'ExampleQuery',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example\Queries\ExampleQuery",
                        'properties' => [
                            ['name' => 'name', 'type' => 'string'],
                        ],
                    ],
                ],
            ],
            'Example2' => [
                'domain' => 'Example2',
                'path' => '/Users/r2luna/code/0..play/brain/tests/Feature/Console/../Fixtures/Brain/Example2',
                'processes' => [
                    [
                        'name' => 'ExampleProcess2',
                        'chain' => true,
                        'tasks' => [
                            [
                                'name' => 'ExampleTask4',
                                'fullName' => "Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4",
                                'queue' => true,
                                'properties' => [],
                            ],
                        ],
                    ],
                ],
                'tasks' => [],
                'queries' => [
                    [
                        'name' => 'ExampleQuery',
                        'fullName' => "Tests\Feature\Fixtures\Brain\Example2\Queries\ExampleQuery",
                        'properties' => [],
                    ],
                ],
            ],
            'Example3' => [
                'domain' => 'Example3',
                'path' => '/Users/r2luna/code/0..play/brain/tests/Feature/Console/../Fixtures/Brain/Example3',
                'processes' => [],
                'tasks' => [],
                'queries' => [],
            ],
        ];

        expect($brain->map)
            ->toMatchArray($data);
    });

});

describe('loadProcessesFor testsuite', function () {
    it('should list all processes in a dir', function () {
        $method = $this->reflection->getMethod('loadProcessesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(1)
            ->and($output)->toMatchArray([
                [
                    'name' => 'ExampleProcess',
                    'chain' => false,
                    'tasks' => [
                        ['name' => 'ExampleTask4', 'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4', 'queue' => true, 'properties' => []],
                    ],
                ],
            ]);
    });

    it('should return an empty array if dir doesnt exists', function () {
        $method = $this->reflection->getMethod('loadProcessesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example3';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(0);
    });

    it('should check if the process is in chain', function () {
        $method = $this->reflection->getMethod('loadProcessesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example2';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(1)
            ->and($output[0])->toMatchArray([
                'name' => 'ExampleProcess2',
                'chain' => true,
            ]);
    });
});

describe('loadTasksFor testsuite', function () {
    it('should load tasks from a given path', function () {
        $method = $this->reflection->getMethod('loadTasksFor');
        $path = __DIR__.'/../Fixtures/Brain/Example';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(4)
            ->and($output)->toMatchArray([
                [
                    'name' => 'ExampleTask',
                    'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask',
                    'queue' => false,
                    'properties' => [
                        ['name' => 'email', 'type' => 'string', 'direction' => 'output'],
                        ['name' => 'paymentId', 'type' => 'int', 'direction' => 'output'],
                    ],
                ],
                [
                    'name' => 'ExampleTask2',
                    'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask2',
                    'queue' => false,
                    'properties' => [
                        ['name' => 'email', 'type' => 'string', 'direction' => 'output'],
                        ['name' => 'paymentId', 'type' => 'int', 'direction' => 'output'],
                        ['name' => 'id', 'type' => 'int', 'direction' => 'input'],
                    ],
                ],
                [
                    'name' => 'ExampleTask3',
                    'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask3',
                    'queue' => false,
                    'properties' => [],
                ],
                [
                    'name' => 'ExampleTask4',
                    'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4',
                    'queue' => true,
                    'properties' => [],
                ],
            ]);
    });

    it('should return an empty array if the directory does not exists', function () {
        $method = $this->reflection->getMethod('loadTasksFor');
        $path = __DIR__.'/../Fixtures/Brain/Example3';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(0);
    });

    it('should check if the task needs to run in a queue', function () {
        $method = $this->reflection->getMethod('loadTasksFor');
        $path = __DIR__.'/../Fixtures/Brain/Example';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(4)
            ->and($output[0])->toMatchArray([
                'name' => 'ExampleTask',
                'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask',
                'queue' => false,
            ])
            ->and($output[3])->toMatchArray([
                'name' => 'ExampleTask4',
                'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4',
                'queue' => true,
            ]);
    });
});

describe('getPropertiesFor testsuite', function () {
    it('should get properties from a dockblock', function () {
        $reflection = new ReflectionClass('\Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask');

        $method = $this->reflection->getMethod('getPropertiesFor');
        $output = $method->invokeArgs($this->object, [$reflection]);

        expect($output)->toHaveCount(2)
            ->and($output)
            ->toMatchArray([
                [
                    'name' => 'email',
                    'type' => 'string',
                    'direction' => 'output',
                ],
                [
                    'name' => 'paymentId',
                    'type' => 'int',
                    'direction' => 'output',
                ],
            ]);
    });

    it('directions should be based on the read and non read properties', function () {
        $reflection = new ReflectionClass('\Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask2');

        $method = $this->reflection->getMethod('getPropertiesFor');
        $output = $method->invokeArgs($this->object, [$reflection]);

        expect($output)->toHaveCount(3)
            ->and($output)
            ->toMatchArray([
                [
                    'name' => 'email',
                    'type' => 'string',
                    'direction' => 'output',
                ],
                [
                    'name' => 'paymentId',
                    'type' => 'int',
                    'direction' => 'output',
                ],
                [
                    'name' => 'id',
                    'type' => 'int',
                    'direction' => 'input',
                ],
            ]);
    });

    it('should return null if there is any tag that is not property-read or property', function () {
        $reflection = new ReflectionClass('\Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask3');

        $method = $this->reflection->getMethod('getPropertiesFor');
        $output = $method->invokeArgs($this->object, [$reflection]);

        expect($output)->toHaveCount(0)
            ->and($output)
            ->toMatchArray([]);
    });

    it('should return null if there is no docblock', function () {
        $reflection = new ReflectionClass('\Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask4');

        $method = $this->reflection->getMethod('getPropertiesFor');
        $output = $method->invokeArgs($this->object, [$reflection]);

        expect($output)->toBeEmpty();
    });
});

describe('loadQueriesFor testsuite', function () {
    it('should load queries from a given path', function () {
        $method = $this->reflection->getMethod('loadQueriesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(1)
            ->and($output[0])
            ->toMatchArray([
                'name' => 'ExampleQuery',
                'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Queries\ExampleQuery',
            ]);
    });

    it('should load all properties', function () {
        $method = $this->reflection->getMethod('loadQueriesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(1)
            ->and($output[0])->toMatchArray([
                'name' => 'ExampleQuery',
                'fullName' => 'Tests\Feature\Fixtures\Brain\Example\Queries\ExampleQuery',
                'properties' => [
                    [
                        'name' => 'name',
                        'type' => 'string',
                    ],
                ],
            ]);
    });

    it('should return an empty array when there is no construct', function () {
        $method = $this->reflection->getMethod('loadQueriesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example2';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(1)
            ->and($output[0])->toMatchArray([
                'name' => 'ExampleQuery',
                'fullName' => 'Tests\Feature\Fixtures\Brain\Example2\Queries\ExampleQuery',
                'properties' => [],
            ]);
    });

    it('should return an empty array if the directory does not exists', function () {
        $method = $this->reflection->getMethod('loadQueriesFor');
        $path = __DIR__.'/../Fixtures/Brain/Example3';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toHaveCount(0);
    });
});

describe('getReflectionClass testsuite', function () {
    it('should create a reflection class from a given string', function () {
        $method = $this->reflection->getMethod('getReflectionClass');
        $path = '\Tests\Feature\Fixtures\QueuedTask';
        $output = $method->invokeArgs($this->object, [$path]);

        expect($output)->toBeInstanceOf(ReflectionClass::class);
    });

    it('should create a reflection class from a SplFileInfo', function () {
        $method = $this->reflection->getMethod('getReflectionClass');
        $path = new SplFileInfo(__DIR__.'/../Fixtures/QueuedTask.php');
        $output = $method->invokeArgs($this->object, [$path]);
        expect($output)->toBeInstanceOf(ReflectionClass::class);
    });
});

describe('getClassFullNameFromFile testsuite', function () {

    it('should get the full name from a file', function () {
        $content = <<<'PHP'
<?php

namespace MyApp\Services;

use Tests\Feature\Fixtures\Brain\Example\Tasks\ExampleTask;

class TestClass
{
    //
}
PHP;

        // Create a temporary file
        $filePath = tempnam(sys_get_temp_dir(), 'TestFile');
        file_put_contents($filePath, $content);

        $method = $this->reflection->getMethod('getClassFullNameFromFile');
        $method->setAccessible(true);

        $output = $method->invokeArgs($this->object, [$filePath]);

        unlink($filePath);

        expect($output)->toBe('\MyApp\Services\TestClass');
    });
});
