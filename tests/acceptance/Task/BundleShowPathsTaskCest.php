<?php

namespace Sweetchuck\Robo\Bundler\Tests\Acceptance\Task;

use Sweetchuck\Robo\Bundler\Test\AcceptanceTester;
use Sweetchuck\Robo\Bundler\Test\Helper\RoboFiles\BundleRoboFile;

class BundleShowPathsTaskCest
{
    public function runInstallSuccess(AcceptanceTester $I)
    {
        $id = 'show:paths';
        $I->runRoboTask($id, BundleRoboFile::class, 'show:paths');
        $I->assertContains("/rdoc-5.1.0\n", $I->getRoboTaskStdOutput($id));
        $I->assertEquals(0, $I->getRoboTaskExitCode($id));
    }
}
