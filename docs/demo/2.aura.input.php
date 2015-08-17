<?php

namespace Ray\Validation;

use Aura\Input\Filter;
use Aura\Input\Form;
use Ray\Di\AbstractModule;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\Injector;
use Ray\Validation\Annotation\AuraInput;

require __DIR__ . '/autoload.php';

class Fake3Form extends AbstractAuraForm
{
    public function init()
    {
        $this->setName('contact');
        $this->setField('name', 'text')
            ->setAttribs([
                'id' => 'name',
                'size' => 20,
                'maxlength' => 20,
            ]);
        /** @var $filter Filter */
        $filter = $this->getFilter();
        $filter->setRule(
            'name',
            'First name must be alphabetic only.',
            function ($value) {
                return ctype_alpha($value);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $form = $this->helper->form();
        $name = $this->get('name');
        $form .= $this->helper->form($name);
        $errorMessage = $this->getFilter()->getMessages('name');
        $form .= implode(',', $errorMessage);
        $form .= $this->helper->tag('/form');

        return $form;
    }
}

class Fake3
{
    public $view = [];

    /**
     * @var AbstractAuraForm
     */
    private $form;

    /**
     * @param AbstractAuraForm $form
     *
     * @Inject()
     * @Named("form3")
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    public function onGet()
    {
        $this->view['form'] = $this->form;

        return $this;
    }

    /**
     * @AuraInput(onFailure="onFailure")
     */
    public function onPost($name)
    {
        return $name;
    }

    public function onFailure($name)
    {
        return $this->onGet();
    }
}

class MyModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new AuraInputModule);
        $this->bind(Form::class)->annotatedWith('form3')->to(Fake3Form::class);
    }
}

/* @var $fake Fake3 */
try {
    $fake = (new Injector(new MyModule))->getInstance(Fake3::class);
} catch (\Exception $e) {
    echo $e;
    exit;
}
$result = $fake->onPost(1);
$expected = '<form method="post" enctype="multipart/form-data"><form method="post" enctype="multipart/form-data" type="text" name="contact[name]" attribs="name   20 20" options="" value="1">First name must be alphabetic only.</form>';
echo((string) $result->view['form'] === $expected ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
