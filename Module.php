<?php

namespace PyramidImageBuilder;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use PyramidImageBuilder\Form\ConfigForm;
use PyramidImageBuilder\Job\Build;

class Module extends AbstractModule
{
    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        if ($settings->get('pyramidimagebuilder_build_when_ingested')) {
            $sharedEventManager->attach(
                'Omeka\Entity\Media',
                'entity.persist.post',
                [$this, 'onMediaPersist']
            );
        }

        $sharedEventManager->attach(
            'Omeka\Entity\Media',
            'entity.remove.post',
            [$this, 'onMediaRemove']
        );
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $settings = $this->getServiceLocator()->get('Omeka\Settings');

        $form = $formElementManager->get(ConfigForm::class);
        $form->setData([
            'build_when_ingested' => $settings->get('pyramidimagebuilder_build_when_ingested'),
            'build_in_background_job' => $settings->get('pyramidimagebuilder_build_in_background_job'),
        ]);

        return $renderer->formCollection($form, false);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $settings = $this->getServiceLocator()->get('Omeka\Settings');

        $form = $formElementManager->get(ConfigForm::class);
        $form->setData($controller->params()->fromPost());
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $formData = $form->getData();
        $settings->set('pyramidimagebuilder_build_when_ingested', $formData['build_when_ingested']);
        $settings->set('pyramidimagebuilder_build_in_background_job', $formData['build_in_background_job']);

        return true;
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function onMediaPersist(Event $event)
    {
        $media = $event->getTarget();

        if (!$media->hasOriginal()) {
            return;
        }

        if (0 !== strncmp($media->getMediaType(), 'image/', 6)) {
            return;
        }

        $services = $this->getServiceLocator();
        $logger = $services->get('Omeka\Logger');
        $settings = $services->get('Omeka\Settings');

        if ($settings->get('pyramidimagebuilder_build_in_background_job')) {
            $jobDispatcher = $services->get('Omeka\Job\Dispatcher');
            $jobArgs = [
                'mediaId' => $media->getId(),
                'overwrite' => true,
            ];
            $jobDispatcher->dispatch(Build::class, $jobArgs);
        } else {
            $builder = $services->get('PyramidImageBuilder\Builder');

            try {
                $builder->build($media, ['overwrite' => true]);
            } catch (\Exception $e) {
                $logger->err(sprintf('PyramidImageBuilder: Failed to build media %d: %s', $media->getId(), $e->getMessage()));
            }
        }
    }

    public function onMediaRemove(Event $event)
    {
        $services = $this->getServiceLocator();
        $logger = $services->get('Omeka\Logger');
        $fileStore = $services->get('Omeka\File\Store');

        $media = $event->getTarget();
        if ($media->hasOriginal()) {
            $storagePath = sprintf('pyramid/%s', $media->getStorageId());
            try {
                $fileStore->delete($storagePath);
            } catch (\Exception $e) {
                $logger->err('PyramidImageBuilder: Failed to delete pyramid image: ' . $e->getMessage());
            }
        }
    }
}
