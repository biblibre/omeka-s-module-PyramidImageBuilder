<?php

namespace PyramidImageBuilder\BuildStrategy\JPEG2000;

use PyramidImageBuilder\BuildStrategy\StrategyInterface;

class ImageMagick implements StrategyInterface
{
    public function build($source, $destination, array $options)
    {
        $command = sprintf('convert %s jp2:%s 2>&1', escapeshellarg($source), escapeshellarg($destination));
        $output = [];
        $result_code = 0;
        exec($command, $output, $result_code);

        if ($result_code !== 0) {
            throw new \Exception(sprintf("Command failed with code %d: %s\nCommand output:\n%s", $result_code, $command, implode("\n", $output)));
        }
    }
}
