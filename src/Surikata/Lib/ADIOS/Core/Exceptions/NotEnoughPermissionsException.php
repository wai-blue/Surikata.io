<?php

namespace ADIOS\Core\Exceptions;

/**
 * Thrown by the custom implementation of \ADIOS\Core\checkPermissionsForAction() method.
 * Blocks rendering of the action's content.
 *
 * @package Exceptions
 */
class NotEnoughPermissionsException extends \Exception { }
