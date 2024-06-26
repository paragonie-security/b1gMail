<?php

namespace Sabre\DAV;

use UnexpectedValueException;

/**
 * This class represents a set of properties that are going to be updated.
 *
 * Usually this is simply a PROPPATCH request, but it can also be used for
 * internal updates.
 *
 * Property updates must always be atomic. This means that a property update
 * must either completely succeed, or completely fail.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class PropPatch {

    /**
     * Properties that are being updated.
     *
     * This is a key-value list. If the value is null, the property is supposed
     * to be deleted.
     *
     * @var array
     */
    protected $mutations;

    /**
     * A list of properties and the result of the update. The result is in the
     * form of a HTTP status code.
     *
     * @var array
     */
    protected $result = [];

    /**
     * This is the list of callbacks when we're performing the actual update.
     *
     * @var array
     */
    protected $propertyUpdateCallbacks = [];

    /**
     * This property will be set to true if the operation failed.
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * Constructor
     *
     * @param array $mutations A list of updates
     */
    function __construct(array $mutations) {

        $this->mutations = $mutations;

    }

    /**
     * Call this function if you wish to handle updating certain properties.
     * For instance, your class may be responsible for handling updates for the
     * {DAV:}displayname property.
     *
     * In that case, call this method with the first argument
     * "{DAV:}displayname" and a second argument that's a method that does the
     * actual updating.
     *
     * It's possible to specify more than one property.
     *
     * @param string|string[] $properties
     * @param callable $callback
     * @return void
     */
    function handle($properties, callable $callback): void {

        $usedProperties = [];
        foreach ((array)$properties as $propertyName) {

            if (array_key_exists($propertyName, $this->mutations) && !isset($this->result[$propertyName])) {

                $usedProperties[] = $propertyName;
                // HTTP Accepted
                $this->result[$propertyName] = 202;
            }

        }

        // Only registering if there's any unhandled properties.
        if (!$usedProperties) {
            return;
        }
        $this->propertyUpdateCallbacks[] = [
            // If the original argument to this method was a string, we need
            // to also make sure that it stays that way, so the commit function
            // knows how to format the arguments to the callback.
            is_string($properties) ? $properties : $usedProperties,
            $callback
        ];

    }

    /**
     * Call this function if you wish to handle _all_ properties that haven't
     * been handled by anything else yet. Note that you effectively claim with
     * this that you promise to process _all_ properties that are coming in.
     *
     * @param callable $callback
     * @return void
     */
    function handleRemaining(callable $callback): void {

        $properties = $this->getRemainingMutations();
        if (!$properties) {
            // Nothing to do, don't register callback
            return;
        }

        foreach ($properties as $propertyName) {
            // HTTP Accepted
            $this->result[$propertyName] = 202;

            $this->propertyUpdateCallbacks[] = [
                $properties,
                $callback
            ];
        }

    }

    /**
     * Sets the result code for one or more properties.
     *
     * @param string|string[] $properties
     * @param int $resultCode
     * @return void
     */
    function setResultCode($properties, $resultCode): void {

        foreach ((array)$properties as $propertyName) {
            $this->result[$propertyName] = $resultCode;
        }

        if ($resultCode >= 400) {
            $this->failed = true;
        }

    }

    /**
     * Sets the result code for all properties that did not have a result yet.
     *
     * @param int $resultCode
     * @return void
     */
    function setRemainingResultCode($resultCode): void {

        $this->setResultCode(
            $this->getRemainingMutations(),
            $resultCode
        );

    }

    /**
     * Returns the list of properties that don't have a result code yet.
     *
     * This method returns a list of property names, but not its values.
     *
     * @return string[]
     */
    function getRemainingMutations() {

        $remaining = [];
        foreach ($this->mutations as $propertyName => $propValue) {
            if (!isset($this->result[$propertyName])) {
                $remaining[] = $propertyName;
            }
        }

        return $remaining;

    }

    /**
     * Returns the list of properties that don't have a result code yet.
     *
     * This method returns list of properties and their values.
     *
     * @return array
     */
    function getRemainingValues() {

        $remaining = [];
        foreach ($this->mutations as $propertyName => $propValue) {
            if (!isset($this->result[$propertyName])) {
                $remaining[$propertyName] = $propValue;
            }
        }

        return $remaining;

    }

    /**
     * Performs the actual update, and calls all callbacks.
     *
     * This method returns true or false depending on if the operation was
     * successful.
     *
     * @return bool
     */
    function commit() {

        // First we validate if every property has a handler
        foreach ($this->mutations as $propertyName => $value) {

            if (!isset($this->result[$propertyName])) {
                $this->failed = true;
                $this->result[$propertyName] = 403;
            }

        }

        foreach ($this->propertyUpdateCallbacks as $callbackInfo) {

            if ($this->failed) {
                break;
            }
            if (is_string($callbackInfo[0])) {
                $this->doCallbackSingleProp($callbackInfo[0], $callbackInfo[1]);
            } else {
                $this->doCallbackMultiProp($callbackInfo[0], $callbackInfo[1]);
            }

        }

        /**
         * If anywhere in this operation updating a property failed, we must
         * update all other properties accordingly.
         */
        if ($this->failed) {

            foreach ($this->result as $propertyName => $status) {
                if ($status === 202) {
                    // Failed dependency
                    $this->result[$propertyName] = 424;
                }
            }

        }

        return !$this->failed;

    }

    /**
     * Executes a property callback with the single-property syntax.
     *
     * @param string $propertyName
     * @param callable $callback
     * @return void
     */
    private function doCallBackSingleProp($propertyName, callable $callback): void {

        $result = $callback($this->mutations[$propertyName]);
        if (is_bool($result)) {
            if ($result) {
                if (is_null($this->mutations[$propertyName])) {
                    // Delete
                    $result = 204;
                } else {
                    // Update
                    $result = 200;
                }
            } else {
                // Fail
                $result = 403;
            }
        }
        if (!is_int($result)) {
            throw new UnexpectedValueException('A callback sent to handle() did not return an int or a bool');
        }
        $this->result[$propertyName] = $result;
        if ($result >= 400) {
            $this->failed = true;
        }

    }

    /**
     * Executes a property callback with the multi-property syntax.
     *
     * @param array $propertyList
     * @param callable $callback
     * @return void
     */
    private function doCallBackMultiProp(array $propertyList, callable $callback): void {

        $argument = [];
        foreach ($propertyList as $propertyName) {
            $argument[$propertyName] = $this->mutations[$propertyName];
        }

        $result = $callback($argument);

        if (is_array($result)) {
            foreach ($propertyList as $propertyName) {
                if (!isset($result[$propertyName])) {
                    $resultCode = 500;
                } else {
                    $resultCode = $result[$propertyName];
                }
                if ($resultCode >= 400) {
                    $this->failed = true;
                }
                $this->result[$propertyName] = $resultCode;

            }
        } elseif ($result === true) {

            // Success
            foreach ($argument as $propertyName => $propertyValue) {
                $this->result[$propertyName] = is_null($propertyValue) ? 204 : 200;
            }

        } elseif ($result === false) {
            // Fail :(
            $this->failed = true;
            foreach ($propertyList as $propertyName) {
                $this->result[$propertyName] = 403;
            }
        } else {
            throw new UnexpectedValueException('A callback sent to handle() did not return an array or a bool');
        }

    }

    /**
     * Returns the result of the operation.
     *
     * @return array
     */
    function getResult() {

        return $this->result;

    }

    /**
     * Returns the full list of mutations
     *
     * @return array
     */
    function getMutations() {

        return $this->mutations;

    }

}
