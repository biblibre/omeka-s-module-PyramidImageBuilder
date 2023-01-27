<?php

namespace PyramidImageBuilder\BuildStrategy\TIFF;

use PyramidImageBuilder\BuildStrategy\StrategyInterface;

class Vips implements StrategyInterface
{
    public function build($source, $destination)
    {
        $command = sprintf('vips tiffsave %s %s --tile --pyramid --compression jpeg 2>&1', escapeshellarg($source), escapeshellarg($destination));
        $output = [];
        $result_code = 0;
        exec($command, $output, $result_code);

        if ($result_code !== 0) {
            throw new \Exception(sprintf("Command failed with code %d: %s\nCommand output:\n%s", $result_code, $command, implode("\n", $output)));
        }
    }
}
