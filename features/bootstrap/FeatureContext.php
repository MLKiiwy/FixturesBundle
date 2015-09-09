<?php

class FeatureContext extends Context
{
    /**
     * @Given I clear directory :path
     */
    public function clearDirectory($path)
    {
        $path = $this->getRealPath($path);

        exec("rm -rf {$path}");
        mkdir($path);
    }
}
