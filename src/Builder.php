<?php

namespace PyramidImageBuilder;

use Exception;
use Omeka\Entity\Media;
use Omeka\File\Store\StoreInterface;
use Omeka\Settings\SettingsInterface;
use PyramidImageBuilder\BuildStrategy\StrategyInterface;

class Builder
{
    protected $fileStore;
    protected $buildStrategy;
    protected $settings;

    public function __construct(StoreInterface $fileStore, StrategyInterface $buildStrategy, SettingsInterface $settings)
    {
        $this->fileStore = $fileStore;
        $this->buildStrategy = $buildStrategy;
        $this->settings = $settings;
    }

    public function build(Media $media, array $options = [])
    {
        $overwrite = $options['overwrite'] ?? false;
        if (!$overwrite) {
            $pyramidLocalPath = $this->getPyramidLocalPath($media);
            if (file_exists($pyramidLocalPath)) {
                return;
            }
        }

        if (!$media->hasOriginal()) {
            throw new Exception('Media cannot be builded because it does not have an original file');
        }

        if (0 !== strncmp($media->getMediaType(), 'image/', 6)) {
            throw new Exception('Media cannot be builded because of its type: ' . $media->getMediaType());
        }

        $source = $this->fileStore->getLocalPath(sprintf('original/%s', $media->getFilename()));
        if (!file_exists($source)) {
            throw new Exception('File does not exist: ' . $source);
        }

        $tempPyramidFile = tempnam(sys_get_temp_dir(), 'pyramid-');
        if ($tempPyramidFile === false) {
            throw new Exception('Failed to create temporary file');
        }

        $tile_size = intval($this->settings->get('pyramidimagebuilder_tile_size')) ?: 256;
        $options = [
            'tile_size' => $tile_size,
        ];

        $this->buildStrategy->build($source, $tempPyramidFile, $options);

        $this->fileStore->put($tempPyramidFile, $this->getPyramidStoragePath($media));

        unlink($tempPyramidFile);
    }

    protected function getPyramidStoragePath(Media $media)
    {
        return sprintf('pyramid/%s', $media->getStorageId());
    }

    protected function getPyramidLocalPath(Media $media)
    {
        return $this->fileStore->getLocalPath($this->getPyramidStoragePath($media));
    }
}
