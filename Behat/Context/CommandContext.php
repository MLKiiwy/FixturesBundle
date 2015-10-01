<?php

namespace LaFourchette\FixturesBundle\Behat\Context;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\Bundle\Bundle as HttpKernelBundle;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandContext extends Context implements KernelAwareContext
{
    /**
     * @var MinkContext
     */
    private $minkContext;

    /**
     * @var Application
     */
    private $application = null;

    /**
     * @var StreamOutput
     */
    private $output = null;

    /**
     * @var int|null
     */
    private $exitCode = null;

    /**
     * @var \Exception|null
     */
    private $exception = null;

    /**
     * @param BeforeScenarioScope $scope
     *
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');

        foreach ($this->getKernel()->getBundles() as $bundle) {
            if ($bundle instanceof HttpKernelBundle) {
                $bundle->registerCommands($this->getApplication());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        parent::setKernel($kernel);
        //If a command has no environment parameter SYMFONY_ENV environment variable will be used
        putenv('SYMFONY_ENV='.$this->getKernel()->getEnvironment());
        $this->application = new Application($this->getKernel());
    }

    /**
     * @return Application
     *
     * @throws \Exception
     */
    private function getApplication()
    {
        if ($this->application == null) {
            throw new \Exception(
                'Application not initialized. Kernel not provided ! '
                .'Register \Behat\Symfony2Extension\Extension on you behat.yml'
            );
        }

        return $this->application;
    }

    /**
     * @param string $commandName
     * @param string $commandArgs
     *
     * @When I run :commandName command
     * @When I run :commandName command with :commandArgs
     */
    public function iRunCommand($commandName, $commandArgs = '')
    {
        $this->executeCommand($commandName, $commandArgs);
    }

    /**
     * @When print command output
     *
     * @throws \Exception
     */
    public function printCommandOutput()
    {
        $this->printDebug($this->getRawCommandOutput());
    }

    /**
     * @When DEBUG: I dump command exception
     */
    public function debugIDumpCommandException()
    {
        if ($this->exception instanceof \Exception) {
            $this->printDebug(
                sprintf(
                    'Exception message : %s'."\n"
                    .'Exception code : %s'."\n"
                    .'Exception trace : '."\n".'%s',
                    $this->exception->getMessage(),
                    $this->exception->getCode(),
                    $this->exception->getTraceAsString()
                )
            );
        } else {
            $this->printDebug('No exception thrown');
        }
    }

    /**
     * @When DEBUG: I dump command exit code
     */
    public function debugIDumpCommandExitCode()
    {
        $this->printDebug(
            sprintf(
                'Command exit code : %s',
                $this->exitCode
            )
        );
    }

    /**
     * @param int $code
     *
     * @throws ExpectationException
     *
     * @Then Command should be successfully executed
     * @Then /^Command exit code should be (?P<code>\-\d+|\d+)$/
     */
    public function commandExitCodeShouldBe($code = 0)
    {
        try {
            \PHPUnit_Framework_Assert::assertEquals(
                $code,
                $this->exitCode
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            throw new ExpectationException(
                sprintf('Command exit code "%s" not equals to "%s" ! Output:%s', $this->exitCode, $code, PHP_EOL.$this->getRawCommandOutput()),
                $this->minkContext->getSession(),
                $e
            );
        }
    }

    /**
     * @param PyStringNode $message
     *
     * @throws ExpectationException
     *
     * @Then Command exception should have been thrown
     * @Then Command exception should have been thrown with message:
     */
    public function commandShouldThrowException(PyStringNode $message = null)
    {
        if (!$this->exception instanceof \Exception) {
            throw new ExpectationException(
                'Command has not thrown exception !',
                $this->minkContext->getSession()
            );
        }
        if (null !== $message) {
            try {
                \PHPUnit_Framework_Assert::assertSame(
                    $message->getRaw(),
                    $this->exception->getMessage()
                );
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                throw new ExpectationException(
                    sprintf(
                        'Command exception message "%s" does not match "%s" !',
                        $this->exception->getMessage(),
                        $message->getRaw()
                    ),
                    $this->minkContext->getSession(),
                    $e
                );
            }
        }
    }

    /**
     * @param PyStringNode $string
     *
     * @throws \Exception
     * @throws ExpectationException
     *
     * @Then Command output should be:
     */
    public function outputShouldBe(PyStringNode $string)
    {
        $commandOutput = $this->getRawCommandOutput();
        $pyStringNodeContent = $this->replaceDates($string->getRaw());

        try {
            \PHPUnit_Framework_Assert::assertSame(
                trim(str_replace(' ', '', $pyStringNodeContent)),
                trim(str_replace(' ', '', $commandOutput))
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            throw new ExpectationException(
                sprintf(
                    'Error : Command output is not like it should be !'."\n"
                    .'Output : '."\n#########>\n".'%s'."\n<#########\n",
                    $commandOutput
                ),
                $this->minkContext->getSession(),
                $e
            );
        }
    }

    /**
     * @param PyStringNode $string
     *
     * @throws \Exception
     * @throws ExpectationException
     *
     * @Then Command output should contains:
     */
    public function outputShouldContain(PyStringNode $string)
    {
        $commandOutput = $this->getRawCommandOutput();
        try {
            \PHPUnit_Framework_Assert::assertContains(
                $string->getRaw(),
                $commandOutput
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            throw new ExpectationException(
                sprintf(
                    'Error : Command output does not contain : '."\n".'%s'."\n"
                    .'Output : '."\n#########>\n".'%s'."\n<#########\n",
                    $string->getRaw(),
                    $commandOutput
                ),
                $this->minkContext->getSession(),
                $e
            );
        }
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getRawCommandOutput()
    {
        if (!$this->output) {
            throw new \Exception('No command output !!');
        }
        rewind($this->output->getStream());

        return stream_get_contents($this->output->getStream());
    }

    /**
     * @param string      $commandName
     * @param string|null $flatCommandArgs
     */
    private function executeCommand($commandName, $flatCommandArgs = null)
    {
        $inputString = trim($commandName);
        $flatCommandArgs = trim($flatCommandArgs);

        if ($flatCommandArgs != '') {
            $inputString .= ' '.$flatCommandArgs;
        }
        $input = new StringInput($inputString);
        $this->output = new StreamOutput(tmpfile());
        $this->exception = null;

        try {
            $this->exitCode = $this->application->doRun($input, $this->output);
        } catch (\Exception $e) {
            $this->exception = $e;
            $this->exitCode = -255;
        }
    }
}
