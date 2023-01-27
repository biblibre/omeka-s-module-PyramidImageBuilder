<?php

namespace PyramidImageBuilder\BuildStrategy;

interface StrategyInterface
{
    public function build($source, $destination);
}
