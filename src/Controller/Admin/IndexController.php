<?php

namespace PyramidImageBuilder\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Stdlib\Message;
use PyramidImageBuilder\Form\BatchBuildForm;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $form = $this->getForm(BatchBuildForm::class);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['batchbuildform_csrf']);

                $job = $this->jobDispatcher()->dispatch('PyramidImageBuilder\Job\BatchBuild', $data);

                $jobUrl = $this->url()->fromRoute('admin/id', [
                    'controller' => 'job',
                    'action' => 'show',
                    'id' => $job->getId(),
                ]);

                $message = new Message(
                    $this->translate('Conversion has started. %s'),
                    sprintf(
                        '<a href="%s">%s</a>',
                        htmlspecialchars($jobUrl),
                        $this->translate('Go to background job')
                    )
                );
                $message->setEscapeHtml(false);
                $this->messenger()->addSuccess($message);

                return $this->redirect()->toRoute(null, ['action' => 'index'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);

        return $view;
    }
}
