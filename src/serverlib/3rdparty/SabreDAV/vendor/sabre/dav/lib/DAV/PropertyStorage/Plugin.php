<?php

namespace Sabre\DAV\PropertyStorage;

use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\PropPatch;
use Sabre\DAV\PropFind;
use Sabre\DAV\INode;

/**
 * PropertyStorage Plugin.
 *
 * Adding this plugin to your server allows clients to store any arbitrary
 * WebDAV property.
 *
 * See:
 *   http://sabre.io/dav/property-storage/
 *
 * for more information.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Plugin extends ServerPlugin {

    /**
     * If you only want this plugin to store properties for a limited set of
     * paths, you can use a pathFilter to do this.
     *
     * The pathFilter should be a callable. The callable retrieves a path as
     * its argument, and should return true or false wether it allows
     * properties to be stored.
     *
     * @var callable
     */
    public $pathFilter;

    /**
     * Creates the plugin
     *
     * @param Backend\BackendInterface $backend
     */
    function __construct(Backend\BackendInterface $backend) {

        $this->backend = $backend;

    }

    /**
     * This initializes the plugin.
     *
     * This function is called by Sabre\DAV\Server, after
     * addPlugin is called.
     *
     * This method should set up the required event subscriptions.
     *
     * @param Server $server
     * @return void
     */
    function initialize(Server $server): void {

        $server->on('propFind',    [$this, 'propFind'], 130);
        $server->on('propPatch',   [$this, 'propPatch'], 300);
        $server->on('afterMove',   [$this, 'afterMove']);
        $server->on('afterUnbind', [$this, 'afterUnbind']);

    }

    /**
     * Called during PROPFIND operations.
     *
     * If there's any requested properties that don't have a value yet, this
     * plugin will look in the property storage backend to find them.
     *
     * @param PropFind $propFind
     * @param INode $node
     * @return void
     */
    function propFind(PropFind $propFind, INode $node): void {

        $path = $propFind->getPath();
        $pathFilter = $this->pathFilter;
        if ($pathFilter && !$pathFilter($path)) return;
        $this->backend->propFind($propFind->getPath(), $propFind);

    }

    /**
     * Called during PROPPATCH operations
     *
     * If there's any updated properties that haven't been stored, the
     * propertystorage backend can handle it.
     *
     * @param string $path
     * @param PropPatch $propPatch
     * @return void
     */
    function propPatch($path, PropPatch $propPatch): void {

        $pathFilter = $this->pathFilter;
        if ($pathFilter && !$pathFilter($path)) return;
        $this->backend->propPatch($path, $propPatch);

    }

    /**
     * Called after a node is deleted.
     *
     * This allows the backend to clean up any properties still in the
     * database.
     *
     * @param string $path
     * @return void
     */
    function afterUnbind($path): void {

        $pathFilter = $this->pathFilter;
        if ($pathFilter && !$pathFilter($path)) return;
        $this->backend->delete($path);

    }

    /**
     * Called after a node is moved.
     *
     * This allows the backend to move all the associated properties.
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    function afterMove($source, $destination): void {

        $pathFilter = $this->pathFilter;
        if ($pathFilter && !$pathFilter($source)) return;
        // If the destination is filtered, afterUnbind will handle cleaning up
        // the properties.
        if ($pathFilter && !$pathFilter($destination)) return;

        $this->backend->move($source, $destination);

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    function getPluginName() {

        return 'property-storage';

    }

    /**
     * Returns a bunch of meta-data about the plugin.
     *
     * Providing this information is optional, and is mainly displayed by the
     * Browser plugin.
     *
     * The description key in the returned array may contain html and will not
     * be sanitized.
     *
     * @return array
     */
    function getPluginInfo() {

        return [
            'name'        => $this->getPluginName(),
            'description' => 'This plugin allows any arbitrary WebDAV property to be set on any resource.',
            'link'        => 'http://sabre.io/dav/property-storage/',
        ];

    }
}
