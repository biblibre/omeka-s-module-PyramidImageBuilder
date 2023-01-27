# Pyramid Image Builder (module for Omeka S)

Pyramid Image Builder creates pyramidal (multi-resolution) images for every image in Omeka S

This can be useful if used in association with IIIF Image servers like
[Cantaloupe] or [IIPImage].

## Build strategies

By default, this module builds tiled pyramidal TIFF with ImageMagick, but there
are other build strategies available. For instance you can use [VIPS], which is
much faster.

To select a build strategy you need to modify your `config/local.config.php` so
it looks like this:

```php
<?php
return [
    /* ... */
    'service_manager' => [
        'aliases' => [
            /* This is the default */
            'PyramidImageBuilder\BuildStrategy' => 'PyramidImageBuilder\BuildStrategy\TIFF\ImageMagick',

            /* Other available strategies: */
            'PyramidImageBuilder\BuildStrategy' => 'PyramidImageBuilder\BuildStrategy\TIFF\Vips',
            'PyramidImageBuilder\BuildStrategy' => 'PyramidImageBuilder\BuildStrategy\JPEG2000\ImageMagick',
        ],
    ],
    /* ... */
];
```

In order to use the `TIFF\Vips` strategy you need [VIPS] version 7.28 or greater.

## Pyramid images location

Pyramid images are stored in `OMEKA_PATH/files/pyramid`

## License

Pyramid Image Builder is distributed under the GNU General Public License,
version 3. The full text of this license is given in the `LICENSE` file.

[Cantaloupe]: https://cantaloupe-project.github.io/
[IIPImage]: https://iipimage.sourceforge.io
[VIPS]: https://github.com/libvips/libvips
