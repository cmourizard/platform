<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Form\Type;

use Symfony\Component\Form\Forms;
use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleTypeTest extends AbstractFlexibleManagerTest
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions(array())
            ->getFormFactory();
        $this->type = new FlexibleType($this->manager, 'text');
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');

        $this->assertEquals(
            'Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('oro_flexibleentity_entity', $this->form->getName());
    }

    /**
     * Assert field name and type
     * @param string $name Field name
     * @param string $type Field type alias
     */
    protected function assertField($name, $type)
    {
        $formType = $this->form->get($name);
        $this->assertInstanceOf('\Symfony\Component\Form\Form', $formType);
        $this->assertEquals($type, $formType->getConfig()->getType()->getInnerType()->getName());
    }
}
