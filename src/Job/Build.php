<?php

namespace PyramidImageBuilder\Job;

use Omeka\Job\AbstractJob;
use Omeka\Entity\Media;

class Build extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $em = $services->get('Omeka\EntityManager');
        $logger = $services->get('Omeka\Logger');
        $builder = $services->get('PyramidImageBuilder\Builder');

        $logger->info('Job started');
        $em->flush();

        $mediaId = $this->getArg('mediaId');
        if (!$mediaId) {
            throw new \Exception('Missing required argument mediaId');
        }

        $media = $em->find(Media::class, $mediaId);
        if (!$media) {
            throw new \Exception('Media does not exist');
        }

        try {
            $options = [
                'overwrite' => $this->getArg('overwrite', false),
            ];
            $builder->build($media, $options);
        } catch (\Exception $e) {
            $logger->err(sprintf('PyramidImageBuilder: Failed to build media %d: %s', $media->getId(), $e->getMessage()));
            $em->flush();
        }

        $logger->info('Job completed');
    }
}
