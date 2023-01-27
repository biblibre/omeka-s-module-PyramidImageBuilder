<?php

namespace PyramidImageBuilder\Job;

use Omeka\Job\AbstractJob;
use Omeka\Entity\Media;

class BatchBuild extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $em = $services->get('Omeka\EntityManager');
        $logger = $services->get('Omeka\Logger');
        $builder = $services->get('PyramidImageBuilder\Builder');

        $logger->info('Job started');
        $em->flush();

        $qb = $em->createQueryBuilder();
        $qb->select('m.id')
           ->from(Media::class, 'm')
           ->where('m.hasOriginal = 1')
           ->andWhere('m.mediaType LIKE :mediaType')
           ->setParameter('mediaType', 'image/%');
        $q = $qb->getQuery();
        $ids = $q->getSingleColumnResult();

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
            } catch (\Exception $e) {
                $logger->err(sprintf('PyramidImageBuilder: Failed to build media %s: %s', $media->getStorageId(), $e->getMessage()));
                $em->flush();
            }
        }

        $logger->info('Job completed');
    }
}
