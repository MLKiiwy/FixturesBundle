<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;

class Context implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel = null;

    /**
     * @var array
     */
    public static $crossScenarioParameterBag = [];

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return KernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Prints beautified debug string.
     *
     * @param string $string debug string
     */
    protected function printDebug($string)
    {
        echo "\n\033[36m|  ".strtr($string, array("\n" => "\n|  "))."\033[0m\n\n";
    }

    /**
     * Replace parameters in the given string.
     *
     * @param string $string
     *
     * @return string
     */
    protected function replaceParameters($string)
    {
        foreach (self::$crossScenarioParameterBag as $name => $value) {
            $string = str_replace('{{ '.$name.' }}', $value, $string);
        }

        return $string;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function replaceDates($string)
    {
        return preg_replace_callback(
            '/##([\w]+)\((.*)\)##/',
            function ($matches) {
                switch ($matches[1]) {
                    case 'now':
                        $dt = new \DateTime();

                        return $dt->format($matches[2]);
                        break;
                }
            },
            $string
        );
    }
}
