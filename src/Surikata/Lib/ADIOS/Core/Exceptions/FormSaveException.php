<?php

namespace ADIOS\Core\Exceptions;

/**
 * Used to display warning to the user if any problem with saving a form using UI/Form
 * action occurs. Thrown by model's formValidate() method.
 *
 * @package Exceptions
 */
class FormSaveException extends \Exception { }
