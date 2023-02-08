<?php

namespace PyramidImageBuilder\Job;

use Exception;
use Omeka\Job\AbstractJob;
use Omeka\Entity\Media;
use PyramidImageBuilder\Builder;
use PyramidImageBuilder\Builder\Exception\AlreadyExistsException;
use PyramidImageBuilder\Builder\Exception\MediaTypeNotAllowedException;
use PyramidImageBuilder\Builder\Exception\FileSizeTooSmallException;

class BatchBuild extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $em = $services->get('Omeka\EntityManager');
        $logger = $services->get('Omeka\Logger');
        $builder = $services->get('PyramidImageBuilder\Builder');
        $settings = $services->get('Omeka\Settings');

        $logger->info('Job started');
        $em->flush();

        $qb = $em->createQueryBuilder();
        $qb->select('m.id')
           ->from(Media::class, 'm')
           ->where('m.hasOriginal = 1')
           ->andWhere('m.mediaType IN (:mediaTypes)')
           ->setParameter('mediaTypes', $settings->get('pyramidimagebuilder_media_types_whitelist', Builder::DEFAULT_MEDIA_TYPES_WHITELIST));
        $q = $qb->getQuery();
        $results = $q->getScalarResult();
        $ids = array_column($results, 'id');

        $builtCount = 0;
        $alreadyExistsCount = 0;
        $fileSizeTooSmallCount = 0;
        $errorCount = 0;
        foreach ($ids as $id) {
            $media = $em->find(Media::class, $id);
            if (!$media) {
                continue;
            }

            try {
                $options = [
                    'overwrite' => $this->getArg('overwrite', false),
                ];
                $builder->build($media, $options);

                ++$builtCount;
            } catch (AlreadyExistsException $e) {
                ++$alreadyExistsCount;
            } catch (FileSizeTooSmallException $e) {
                ++$fileSizeTooSmallCount;
            } catch (Exception $e) {
                ++$errorCount;
                $logger->err(sprintf('PyramidImageBuilder: Failed to build media %s: %s', $media->getStorageId(), $e->getMessage()));
                $em->flush();
            }
        }

        $logger->info(sprintf('Pyramid images built: %d', $builtCount));
        if ($alreadyExistsCount) {
            $logger->info(sprintf('Media skipped because a pyramid image already exists: %d', $alreadyExistsCount));
        }
        if ($fileSizeTooSmallCount) {
            $logger->info(sprintf('Media skipped because file size is smaller than the minimum: %d', $fileSizeTooSmallCount));
        }
        $logger->info(sprintf('Errors: %d', $errorCount));

        $logger->info('Job completed');
    }
}
