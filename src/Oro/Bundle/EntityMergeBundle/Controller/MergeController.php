<?php

namespace Oro\Bundle\EntityMergeBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\EntityMergeBundle\Data\EntityData;
use Oro\Bundle\EntityMergeBundle\Data\EntityDataFactory;
use Oro\Bundle\EntityMergeBundle\Doctrine\DoctrineHelper;
use Oro\Bundle\EntityMergeBundle\Exception\ValidationException;
use Oro\Bundle\EntityMergeBundle\Form\Type\MergeType;
use Oro\Bundle\EntityMergeBundle\Model\EntityMerger;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
// TODO: change to Symfony\Component\Validator\Validator\ValidatorInterface in scope of BAP-15236
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @Route("/merge")
 */
class MergeController extends Controller
{

    /**
     * @Route("/{gridName}/massAction/{actionName}", name="oro_entity_merge_massaction")
     * @AclAncestor("oro_entity_merge")
     * @Template("OroEntityMergeBundle:Merge:merge.html.twig")
     * @param Request $request
     * @param string $gridName
     * @param string $actionName
     * @return array|RedirectResponse
     */
    public function mergeMassActionAction(Request $request, $gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_entity_merge.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $request);

        $entityData = $this->getEntityDataFactory()->createEntityData(
            $response->getOption('entity_name'),
            $response->getOption('entities')
        );

        return $this->mergeAction($request, $entityData);
    }

    /**
     * @Route(name="oro_entity_merge")
     * @Acl(
     *      id="oro_entity_merge",
     *      label="oro.entity_merge.acl.merge",
     *      type="action",
     *      category="entity"
     * )
     * @Template()
     * @param Request $request
     * @param EntityData|null $entityData
     * @return array|RedirectResponse
     */
    public function mergeAction(Request $request, EntityData $entityData = null)
    {
        if (!$entityData) {
            $className = $request->get('className');
            $ids = (array)$request->get('ids');

            $entityData = $this->getEntityDataFactory()->createEntityDataByIds($className, $ids);
        } else {
            $className = $entityData->getClassName();
        }

        // TODO: change to $this->getValidator()->validate($entityData, null, ['validateCount']) in scope of BAP-15236
        $constraintViolations = $this->getValidator()->validate($entityData, ['validateCount']);
        if ($constraintViolations->count()) {
            foreach ($constraintViolations as $violation) {
                /* @var ConstraintViolation $violation */
                $this->get('session')->getFlashBag()->add(
                    'error',
                    $violation->getMessage()
                );
            }

            return $this->redirect($this->generateUrl($this->getEntityIndexRoute($entityData->getClassName())));
        }

        $form = $this->createForm(
            MergeType::class,
            $entityData,
            array(
                'metadata' => $entityData->getMetadata(),
                'entities' => $entityData->getEntities(),
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $merger = $this->getEntityMerger();

                try {
                    $this->getEntityManager()->transactional(
                        function () use ($merger, $entityData) {
                            $merger->merge($entityData);
                        }
                    );
                } catch (ValidationException $exception) {
                    foreach ($exception->getConstraintViolations() as $violation) {
                        /* @var ConstraintViolation $violation */
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            $violation->getMessage()
                        );
                    }
                }


                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('oro.entity_merge.controller.merged_successful')
                );

                return $this->redirect(
                    $this->generateUrl(
                        $this->getEntityViewRoute($entityData->getClassName()),
                        array('id' => $entityData->getMasterEntity()->getId())
                    )
                );
            }
        }

        return array(
            'formAction' => $this->generateUrl(
                'oro_entity_merge',
                array(
                    'className' => $className,
                    'ids' => $this->getDoctineHelper()->getEntityIds($entityData->getEntities()),
                )
            ),
            'entityLabel' => $entityData->getMetadata()->get('label'),
            'cancelPath' => $this->generateUrl($this->getEntityIndexRoute($className)),
            'form' => $form->createView()
        );
    }

    /**
     * Get route name for entity view page by class name
     *
     * @param string $className
     * @return string
     */
    protected function getEntityViewRoute($className)
    {
        return $this->getConfigManager()->getEntityMetadata($className)->routeView;
    }

    /**
     * Get route name for entity index page by class name
     *
     * @param string $className
     * @return string
     */
    protected function getEntityIndexRoute($className)
    {
        return $this->getConfigManager()->getEntityMetadata($className)->routeName;
    }

    /**
     * @return \Oro\Bundle\EntityConfigBundle\Config\ConfigManager
     */
    protected function getConfigManager()
    {
        return $this->get('oro_entity_config.config_manager');
    }

    /**
     * @return EntityDataFactory
     */
    protected function getEntityDataFactory()
    {
        return $this->get('oro_entity_merge.data.entity_data_factory');
    }

    /**
     * @return DoctrineHelper
     */
    protected function getDoctineHelper()
    {
        return $this->get('oro_entity_merge.doctrine_helper');
    }

    /**
     * @return EntityMerger
     */
    protected function getEntityMerger()
    {
        return $this->get('oro_entity_merge.merger');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->get('validator');
    }
}
