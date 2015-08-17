<?php
/**
 * This file is part of the Ray.ValidateModule package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Validation;

use Aura\Input\Form;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Validation\Annotation\AuraInput;
use Ray\Validation\Exception\InvalidArgumentException;

class AuraInputInterceptor implements MethodInterceptor
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function invoke(MethodInvocation $invocation)
    {
        $params = $invocation->getMethod()->getParameters();
        $args = $invocation->getArguments();
        $submit = [];
        foreach ($params as $param) {
            $array = (array) $args;
            $submit[$param->getName()] = array_shift($array);
        }
        $object = $invocation->getThis();

        $class = new \ReflectionObject($object);
        $prop = $class->getProperty('form');
        $prop->setAccessible(true);
        $form = $prop->getValue($object);
        $isValid = $this->isValidForm($form, $submit);
        if ($isValid === true) {
            // validation success
            return $invocation->proceed();
        }
        /* @var $auraInput AuraInput */
        $auraInput = $this->reader->getMethodAnnotation($invocation->getMethod(), AuraInput::class);
        $args = (array) $invocation->getArguments();

        return call_user_func_array([$invocation->getThis(), $auraInput->onFailure], $args);
    }

    public function isValidForm(Form $form, array $submit)
    {
        $form->fill($submit);
        $isValid = $form->filter();

        return $isValid;
    }
}
