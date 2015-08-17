<?php
/**
 * This file is part of the Ray.ValidateModule
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Validation\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class AuraForm
{
    /**
     * @var string
     */
    public $value;
}
