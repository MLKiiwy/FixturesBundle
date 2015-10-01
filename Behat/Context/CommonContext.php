<?php

namespace LaFourchette\FixturesBundle\Behat\Context;

use Symfony\Component\HttpKernel\Profiler\Profiler;

class CommonContext extends Context
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Profiler $profiler
     */
    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * @Given I enable the profiler
     *
     * @throws \Exception
     */
    public function iEnableTheProfiler()
    {
        if ($this->profiler) {
            $this->profiler->enable();
        } else {
            throw new \Exception('cannot enable profiler');
        }
    }

    /**
     * @Given I clear directory :path
     */
    public function clearDirectory($path)
    {
        $path = $this->getRealPath($path);

        exec("rm -rf {$path}");
        mkdir($path);
    }

    /**
     * @param string $filePath
     *
     * @return string
     */
    protected function getRealPath($filePath)
    {
        if (strlen($filePath) > 1 && !in_array($filePath[0], array('.', '/'))) {
            $filePath = __DIR__.'/../'.$filePath;
        }

        return $filePath;
    }
}
