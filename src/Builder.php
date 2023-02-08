<?php

namespace PyramidImageBuilder;

use Exception;
use Omeka\Entity\Media;
use Omeka\File\Store\StoreInterface;
use Omeka\Settings\SettingsInterface;
use PyramidImageBuilder\BuildStrategy\StrategyInterface;
use PyramidImageBuilder\Builder\Exception\AlreadyExistsException;
use PyramidImageBuilder\Builder\Exception\MediaTypeNotAllowedException;

class Builder
{
    const DEFAULT_MEDIA_TYPES_WHITELIST = [
        'image/jp2',
        'image/jpeg',
        'image/png',
        'image/tiff',
        'image/webp',
    ];

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
        $this->assertMediaIsAcceptable($media);

        $overwrite = $options['overwrite'] ?? false;
        if (!$overwrite) {
            $pyramidLocalPath = $this->getPyramidLocalPath($media);
            if (file_exists($pyramidLocalPath)) {
                throw new AlreadyExistsException("File already exists: $pyramidLocalPath");
            }
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

    public function assertMediaIsAcceptable(Media $media)
    {
        if (!$media->hasOriginal()) {
            throw new Exception('Media cannot be built because it does not have an original file');
        }

        $media_types_whitelist = $this->settings->get('pyramidimagebuilder_media_types_whitelist', self::DEFAULT_MEDIA_TYPES_WHITELIST);
        if (!in_array($media->getMediaType(), $media_types_whitelist)) {
            throw new MediaTypeNotAllowedException('Media type is not in whitelist: ' . $media->getMediaType());
        }
    }

    public function isMediaAcceptable(Media $media)
    {
        try {
            $this->assertMediaIsAcceptable($media);

            return true;
        } catch (Exception $e) {
        }

        return false;
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
