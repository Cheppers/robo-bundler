<?php

namespace Cheppers\Robo\Bundler\Tests\Unit\Task;

use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Bundler\Task\BundleShowPathsTask;
use Cheppers\Robo\Bundler\Test\Helper\Dummy\Output as DummyOutput;
use Cheppers\Robo\Bundler\Test\Helper\Dummy\Process as DummyProcess;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Robo\Robo;

class BundleShowPathsTaskTest extends Unit
{
    /**
     * @var \Cheppers\Robo\Bundler\Test\UnitTester
     */
    protected $tester;

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'bundle show --paths',
                [],
            ],
            'workingDirectory' => [
                "cd 'my-dir' && bundle show --paths",
                [
                    'workingDirectory' => 'my-dir',
                ],
            ],
            'workingDirectory with ENV' => [
                "cd 'my-dir' && BUNDLE_GEMFILE='../myGemfile' bundle show --paths",
                [
                    'workingDirectory' => 'my-dir',
                    'bundleGemFile' => '../myGemfile',
                ],
            ],
            'gemFile' => [
                "bundle show --paths --gemfile='myGemfile'",
                [
                    'gemFile' => 'myGemfile',
                ],
            ],
            'bundleExecutable' => [
                'my-bundle show --paths',
                [
                    'bundleExecutable' => 'my-bundle',
                ],
            ],
            'no-color true' => [
                'bundle show --paths --no-color',
                [
                    'noColor' => true,
                ],
            ],
            'no-color false' => [
                'bundle show --paths --no-no-color',
                [
                    'noColor' => false,
                ],
            ],
            'common ones' => [
                "cd 'my-dir' && BUNDLE_GEMFILE='Gemfile.my.rb' bundle show --paths",
                [
                    'workingDirectory' => 'my-dir',
                    'bundleGemFile' => 'Gemfile.my.rb',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new BundleShowPathsTask($options);
        $this->tester->assertEquals($expected, $task->getCommand());
    }

    public function casesRun(): array
    {
        return [
            'success' => [
                [
                    'exitCode' => 0,
                    'paths' => [
                        '/a/b/c-1.0.0',
                        '/d/e/f-2.0.0',
                    ],
                ],
                ['workingDirectory' => 'success'],
            ],
            'fail' => [
                [
                    'exitCode' => 1,
                    'paths' => [],
                ],
                ['workingDirectory' => 'fail'],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(array $expected, array $options = [], array $processProphecy = []): void
    {
        $processProphecy += [
            'exitCode' => $expected['exitCode'],
            'stdOutput' => implode("\n", $expected['paths']) . "\n",
            'stdError' => '',
        ];

        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput([]);

        $assetJar = new AssetJar();
        $options += [
            'assetJar' => $assetJar,
            'assetJarMapping' => ['paths' => ['bundleShowPaths', 'paths']],
        ];

        /** @var \Cheppers\Robo\Bundler\Task\BundleShowPathsTask $task */
        $task = Stub::construct(
            BundleShowPathsTask::class,
            [$options],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = $processProphecy;

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();

        $this->tester->assertEquals(
            $expected['exitCode'],
            $result->getExitCode(),
            'Exit code is different than the expected.'
        );

        $this->tester->assertEquals(
            $expected['paths'],
            $task->getAssetJarValue('paths'),
            'AssetJar content: paths'
        );

        $this->tester->assertEquals(
            $expected['paths'],
            $result['paths'],
            'Result content: paths'
        );
    }
}