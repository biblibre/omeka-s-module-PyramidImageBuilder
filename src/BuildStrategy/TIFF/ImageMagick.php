<?php

namespace PyramidImageBuilder\BuildStrategy\TIFF;

use PyramidImageBuilder\BuildStrategy\StrategyInterface;

class ImageMagick implements StrategyInterface
{
    public function build($source, $destination, array $options)
    {
        $tile_size = $options['tile_size'];

        $command = sprintf(
            'convert %1$s -define tiff:tile-geometry=%3$sx%3$s -compress jpeg ptif:%2$s 2>&1',
            escapeshellarg($source),
            escapeshellarg($destination),
            escapeshellarg($tile_size)
        );
        $output = [];
        $result_code = 0;
        exec($command, $output, $result_code);

        if ($result_code !== 0) {
            throw new \Exception(sprintf("Command failed with code %d: %s\nCommand output:\n%s", $result_code, $command, implode("\n", $output)));
        }
    }
}
