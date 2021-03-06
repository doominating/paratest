<?php

declare(strict_types=1);

class SqliteRunnerTest extends FunctionalTestBase
{
    const TEST_METHODS_PER_CLASS = 5;
    const TEST_CLASSES = 6;

    public function testResultsAreCorrect()
    {
        $generator = new TestGenerator();
        $generator->generate(self::TEST_CLASSES, self::TEST_METHODS_PER_CLASS);

        $proc = $this->invokeParatest($generator->path, [
            'runner' => 'SqliteRunner',
            'processes' => 3,
        ]);

        $expected = self::TEST_CLASSES * self::TEST_METHODS_PER_CLASS;
        $this->assertTestsPassed($proc, $expected, $expected);
    }

    public function testMultiLineClassDeclarationWithFilenameDifferentThanClassnameIsSupported()
    {
        $this->assertTestsPassed($this->invokeParatest('special-classes', [
            'runner' => 'SqliteRunner',
            'processes' => 3,
        ]));
    }

    public function testRunningFewerTestsThanTheWorkersIsPossible()
    {
        $generator = new TestGenerator();
        $generator->generate(1, 1);

        $proc = $this->invokeParatest($generator->path, [
            'runner' => 'SqliteRunner',
            'processes' => 2,
        ]);

        $this->assertTestsPassed($proc, 1, 1);
    }

    public function testExitCodes()
    {
        $options = [
            'runner' => 'SqliteRunner',
            'processes' => 1,
        ];
        $proc = $this->invokeParatest('wrapper-runner-exit-code-tests/ErrorTest.php', $options);
        $output = $proc->getOutput();

        $this->assertContains('Tests: 1', $output);
        $this->assertContains('Failures: 0', $output);
        $this->assertContains('Errors: 1', $output);
        $this->assertEquals(2, $proc->getExitCode());

        $proc = $this->invokeParatest('wrapper-runner-exit-code-tests/FailureTest.php', $options);
        $output = $proc->getOutput();

        $this->assertContains('Tests: 1', $output);
        $this->assertContains('Failures: 1', $output);
        $this->assertContains('Errors: 0', $output);
        $this->assertEquals(1, $proc->getExitCode());

        $proc = $this->invokeParatest('wrapper-runner-exit-code-tests/SuccessTest.php', $options);
        $output = $proc->getOutput();

        $this->assertContains('OK (1 test, 1 assertion)', $output);
        $this->assertEquals(0, $proc->getExitCode());

        $options['processes'] = 3;
        $proc = $this->invokeParatest('wrapper-runner-exit-code-tests', $options);
        $output = $proc->getOutput();
        $this->assertContains('Tests: 3', $output);
        $this->assertContains('Failures: 1', $output);
        $this->assertContains('Errors: 1', $output);
        $this->assertEquals(2, $proc->getExitCode()); // There is at least one error so the exit code must be 2
    }
}
