<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Validation;

use Aura\Html\HelperLocator;
use Aura\Html\HelperLocatorFactory;
use Aura\Input\Form;
use Ray\Di\Di\Inject;

abstract class AbstractAuraForm extends Form
{
    /**
     * @var HelperLocator
     */
    protected $helper;

    /**
     * @Inject
     */
    public function setFormHelper(HelperLocatorFactory $factory)
    {
        $this->helper = $factory->newInstance();
    }

    /**
     * Return input element html
     *
     * @param string $input
     *
     * @return string
     * @throws \Aura\Input\Exception\NoSuchInput
     */
    public function input($input)
    {
        return $this->helper->input($this->get($input));
    }

    /**
     * Return error message
     *
     * @param string $input
     * @param string $format
     * @param string $layout
     *
     * @return string
     */
    public function error($input, $format = '%s', $layout = '%s')
    {
        $errorMessages = $this->getFilter()->getMessages($input);
        array_filter($errorMessages, function (&$item) use ($format) {
            $item = sprintf($format, $item);
        });
        $errors = implode('', $errorMessages);

        return  sprintf($layout, $errors);
    }
}
