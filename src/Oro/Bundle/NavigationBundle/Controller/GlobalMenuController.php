<?php

namespace Oro\Bundle\NavigationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/menu/global")
 */
class GlobalMenuController extends AbstractMenuController
{
    /**
     * @Route("/", name="oro_navigation_global_menu_index")
     * @Template
     * @AclAncestor("oro_navigation_manage_menus")
     *
     * @return array
     */
    public function indexAction()
    {
        return $this->index($this->getScope());
    }

    /**
     * @Route("/{menuName}", name="oro_navigation_global_menu_view")
     * @Template
     * @AclAncestor("oro_navigation_manage_menus")
     *
     * @param string $menuName
     *
     * @return array
     */
    public function viewAction($menuName)
    {
        return $this->view($menuName, $this->getScope());
    }

    /**
     * @Route("/{menuName}/create/{parentKey}", name="oro_navigation_global_menu_create")
     * @Template("OroNavigationBundle:GlobalMenu:update.html.twig")
     * @AclAncestor("oro_navigation_manage_menus")
     *
     * @param string $menuName
     * @param string|null $parentKey
     *
     * @return array|RedirectResponse
     */
    public function createAction($menuName, $parentKey = null)
    {
        return parent::create($menuName, $parentKey, $this->getScope());
    }

    /**
     * @Route("/{menuName}/update/{key}", name="oro_navigation_global_menu_update")
     * @Template
     * @AclAncestor("oro_navigation_manage_menus")
     *
     * @param string $menuName
     * @param string $key
     *
     * @return array|RedirectResponse
     */
    public function updateAction($menuName, $key)
    {
        return parent::update($menuName, $key, $this->getScope());
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAcl()
    {
        if (!$this->get('oro_security.security_facade')->isGranted('oro_config_system')) {
            throw $this->createAccessDeniedException();
        }
    }
}
