<?php
/*
 * b1gMail
 * Copyright (c) 2021 Patrick Schlangen et al
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

if (!defined('B1GMAIL_INIT')) {
    die('Directly calling this file is not supported');
}

define('BMPLUGIN_DEFAULT', 1);
define('BMPLUGIN_WIDGET', 2);
define('BMPLUGIN_FILTER', 3);

define('BMWIDGET_START', 1);
define('BMWIDGET_ORGANIZER', 2);

define('PLUGIN_USERID', -1);

/**
 * plugin base class.
 */
class BMPlugin
{
    public $type = BMPLUGIN_DEFAULT;
    public $name = 'Plugin base class';
    public $description = 'Plugin description';
    public $version = '1.0';
    public $author = 'b1gMail Project';
    public $website = false;
    public $id = 0;
    public $tplFromModDir = true;
    public $installed = false;
    public $paused = false;
    public $admin_pages = false;
    public $admin_page_title = 'Plugin base';
    public $admin_page_icon = '';
    public $internal_name = 'BMPlugin';
    public $update_url = false;
    public $_groupOptions = [];
    public $order = 0;

    //
    // setup routines
    //

    /**
     * install handler
     * must return true on success.
     *
     * @return bool
     */
    public function Install()
    {
        return true;
    }

    /**
     * uninstall handler
     * must return true on success.
     *
     * @return bool
     */
    public function Uninstall()
    {
        return true;
    }

    //
    // update routines
    //

    /**
     * check if $ver1 is newer as $ver2.
     *
     * @param string $ver1
     * @param string $ver2
     *
     * @return bool
     */
    public function IsVersionNewer($ver1, $ver2)
    {
        $version1Parts = explode('.', $ver1);
        $version2Parts = explode('.', $ver2);

        $count = max(count($version1Parts), count($version2Parts));

        if (count($version1Parts) < $count) {
            $version1Parts = array_pad($version1Parts, $count, 0);
        }
        if (count($version2Parts) < $count) {
            $version2Parts = array_pad($version2Parts, $count, 0);
        }

        for ($i = 0; $i < $count; ++$i) {
            if ($version1Parts[$i] == $version2Parts[$i]) {
                continue;
            } elseif ($version1Parts[$i] > $version2Parts[$i]) {
                return true;
            } elseif ($version1Parts[$i] < $version2Parts[$i]) {
                return false;
            }
        }

        return false;
    }

    /**
     * check for updates.
     *
     * @param string $latestVersion Contains latest version, if successfuly retrieved
     *
     * @return int BM_UPDATE_* valur
     */
    public function CheckForUpdates(&$latestVersion)
    {
        if (empty($this->update_url)) {
            return BM_UPDATE_UNKNOWN;
        }

        if (!class_exists('BMHTTP')) {
            include B1GMAIL_DIR.'serverlib/http.class.php';
        }

        $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s',
            $this->update_url,
            urlencode($this->internal_name),
            urlencode(B1GMAIL_VERSION));
        $http = _new('BMHTTP', [$queryURL]);
        $queryResult = @unserialize($http->DownloadToString());

        if (!is_array($queryResult) || !isset($queryResult['latestVersion'])
            || $queryResult['internalName'] != $this->internal_name) {
            return BM_UPDATE_UNKNOWN;
        }

        $latestVersion = $queryResult['latestVersion'];

        if ($this->IsVersionNewer($latestVersion, $this->version)) {
            return BM_UPDATE_AVAILABLE;
        }

        return BM_UPDATE_NOT_AVAILABLE;
    }

    //
    // handlers
    //









































    // added in 7.0.0-PL1


    // added in 7.0.0-PL1


    // added in 7.0.0-PL1


    // added in 7.0.0-PL1






























    /**
     *
     * on load.
     *
     * @return void
     */
    public function OnLoad()
    {
    }



    /**
     * get notices for ACP.
     *
     * @return array
     */
    public function getNotices()
    {
        return [];
    }









    //
    // widget handlers
    //

    public $widgetTemplate = false;
    public $widgetTitle = 'Default title';
    public $widgetIcon = false;
    public $widgetPrefs = false;
    public $widgetPrefsWidth = 320;
    public $widgetPrefsHeight = 240;

    /**
     * return if widget is suitable for this page.
     *
     * @param int $for Page (BMWIDGET_-constant)
     *
     * @return bool
     */
    public function isWidgetSuitable($for)
    {
        return false;
    }









    //
    // internal functions
    //

    /**
     * register a group option.
     *
     * @param string $key
     * @param int    $type
     * @param string $desc
     * @param string $options
     * @param string $default
     */
    public function RegisterGroupOption($key, $type = FIELD_TEXT, $desc, $options = '', $default = ''): void
    {
        $this->_groupOptions[$key] = [
            'type' => $type,
            'options' => $options,
            'desc' => $desc,
            'default' => $default,
        ];
    }

    /**
     * get group option value.
     *
     * @param string $key
     * @param int    $group
     *
     * @return string
     */
    public function GetGroupOptionValue($key, $group = 0)
    {
        global $plugins, $groupRow;

        return $plugins->GetGroupOptionValue($group == 0 ? $groupRow['id'] : $group,
            $this->internal_name,
            $key,
            $this->_groupOptions[$key]['default']);
    }

    /**
     * get admin page link.
     *
     * @return string
     */
    public function _adminLink(bool $withSID = false)
    {
        return 'plugin.page.php?plugin='.$this->internal_name.($withSID ? '&sid='.session_id() : '');
    }

    /**
     * get resource path.
     *
     * @param string $type Type
     */
    public function _resourcePath(string $template, $type)
    {
        global $plugins;

        return $plugins->pluginResourcePath($template, $this->internal_name, $type);
    }

    /**
     * get template path.
     *
     * @param string $template Template
     */
    public function _templatePath($template)
    {
        return $this->_resourcePath($template, 'template');
    }

    /**
     * close widget prefs.
     *
     * @param $reload Reload dashboard?
     */
    public function _closeWidgetPrefs($reload = true): void
    {
        echo '<script>'."\n";
        echo '<!--'."\n";
        if ($reload) {
            echo '	parent.document.location.reload();'."\n";
        } else {
            echo '	parent.hideOverlay();'."\n";
        }
        echo '//-->'."\n";
        echo '</script>'."\n";

        exit();
    }




}

/**
 * plugin package reader/installer.
 */
class BMPluginPackage
{
    public $_magic = 'B1GPLUGIN100!';
    public $_fp;
    public $_parsed;
    public $_fileTypes;
    public $_parseResult;
    public $metaInfo;
    public $files;
    public $signature;



    /**
     * parse package file.
     *
     * @return bool
     */
    public function ParseFile()
    {
        // already parsed?
        if ($this->_parsed) {
            return $this->_parseResult;
        }

        // init fp
        if (!is_resource($this->_fp)) {
            return false;
        }
        fseek($this->_fp, 0, SEEK_SET);

        // read magic
        $magic = fread($this->_fp, strlen($this->_magic));
        if ($magic == $this->_magic) {
            // get signature + data
            $rawDataSignature = fread($this->_fp, 32);
            $rawData = '';
            while (!feof($this->_fp)) {
                $rawData .= fread($this->_fp, 4096);
            }

            // verify signature
            if (md5($rawData) === $rawDataSignature) {
                // uncompress data
                if ($inflatedData = gzinflate($rawData)) {
                    // free raw data
                    unset($rawData);

                    // unserialize
                    $pluginData = @unserialize($inflatedData);
                    if (is_array($pluginData)
                        && isset($pluginData['meta'])
                        && isset($pluginData['files'])
                        && isset($pluginData['files']['plugins'])
                        && isset($pluginData['files']['templates'])
                        && isset($pluginData['files']['images'])) {
                        unset($inflatedData);
                        $this->signature = $rawDataSignature;
                        $this->metaInfo = $pluginData['meta'];
                        $this->files = $pluginData['files'];
                        $this->_parsed = true;
                        unset($pluginData);

                        // return
                        $this->_parseResult = true;

                        return true;
                    }
                }
            }
        }

        // something failed
        $this->_parseResult = false;
        $this->_parsed = true;

        return false;
    }

    /**
     *
     * verify a signature / get signature type.
     *
     * @param string $signature Signature
     *
     * @return false|int SIGNATURE_-constant or false
     */
    public static function VerifySignature($signature = ''): int|false
    {
        // query signature server
        $res = QuerySignatureServer('verifyPluginSignature', ['signature' => $signature]);
        if ($res['type'] == 'response' && isset($res['sigType']) && isset($res['signature'])
            && $res['signature'] == $signature) {
            return (int) $res['sigType'];
        } else {
            return false;
        }
    }

    /**
     * check if package is already installed.
     *
     * @param string $signature Signature, if called statically
     *
     * @return bool
     */
    public function AlreadyInstalled($signature = '')
    {
        global $db;

        // not called statically?
        if ($signature == '') {
            $signature = $this->signature;
        }

        // lookup
        $res = $db->Query('SELECT COUNT(*) FROM {pre}mods WHERE signature=?',
            $signature);
        list($rowCount) = $res->FetchArray(MYSQLI_NUM);
        $res->Free();

        return $rowCount != 0;
    }
	

    /**
     *
     * install package (step 1 = remove old package).
     */
    public function InstallStep1(): bool
    {
        global $db, $cacheManager, $plugins;

        // already installed?
        if ($this->AlreadyInstalled()) {
            return false;
        }

        // delete existing files
        foreach ($this->files as $fileType => $fileItems) {
            if (!isset($this->_fileTypes[$fileType])) {
                continue;
            }

            // delete files
            foreach ($fileItems as $fileName => $fileContents) {
                if (trim($fileName) == '') {
                    continue;
                }

                $fileName = $this->_fileTypes[$fileType].str_replace(['..', '/'], '', $fileName);
                $filePath = B1GMAIL_DIR.$fileName;

                if (file_exists($filePath)) {
                    @chmod($filePath, 0666);

                    if (!@unlink($filePath)) {
                        $myFP = @fopen($filePath, 'wb');
                        if ($myFP) {
                            @ftruncate($myFP, 0);
                            @fclose($myFP);
                        }
                    }
                }
            }
        }

        // remove DB entries
        foreach ($this->metaInfo['classes'] as $className) {
            $db->Query('DELETE FROM {pre}mods WHERE modname=?',
                $className);
        }

        // empty cache
        $cacheManager->Delete('dbPlugins_v2');

        return true;
    }

    /**
     * install package (step 2 = install new package).
     *
     * @return bool
     */
    public function InstallStep2()
    {
        global $db, $cacheManager, $plugins;

        // already installed?
        if ($this->AlreadyInstalled()) {
            return false;
        }

        // iterate file types
        $installedFiles = [];
        $pluginFiles = [];
        foreach ($this->files as $fileType => $fileItems) {
            if (!isset($this->_fileTypes[$fileType])) {
                continue;
            }

            // create files
            foreach ($fileItems as $fileName => $fileContents) {
                if (trim($fileName) == '') {
                    continue;
                }

                $fileName = $this->_fileTypes[$fileType].str_replace(['..', '/'], '', $fileName);
                $filePath = B1GMAIL_DIR.$fileName;

                if (!file_exists($filePath)
                    || strpos(strtolower($filePath), '.php') !== false) {
                    $myFP = fopen($filePath, 'wb');
                    if ($myFP) {
                        ftruncate($myFP, 0);
                        fwrite($myFP, $fileContents);
                        fclose($myFP);

                        // try to chmod
                        @chmod($filePath, 0666);

                        // add to installed files list
                        $installedFiles[] = $fileName;

                        // add to plugin list
                        if ($fileType == 'plugins') {
                            $pluginFiles[] = $filePath;
                        }
                    }
                }
            }
        }

        // put to DB
        foreach ($this->metaInfo['classes'] as $className) {
            $db->Query('REPLACE INTO {pre}mods(modname,installed,packageName,signature,files) VALUES(?,?,?,?,?)',
                $className,
                0,
                $this->metaInfo['name'],
                $this->signature,
                serialize($installedFiles));
        }

        // include
        foreach ($pluginFiles as $pluginFile) {
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($pluginFile, true);
            }
            if (!include($pluginFile)) {
                DisplayError(0x11, 'Plugin cannot be loaded', 'A plugin cannot be loaded.',
                                sprintf("Module:\n%s", basename($pluginFile)), __FILE__, __LINE__,500);
                die();
            }
        }

        // install
        foreach ($this->metaInfo['classes'] as $className) {
            $plugins->activatePlugin($className);
        }

        // empty cache
        $cacheManager->Delete('dbPlugins_v2');

        // return
        return true;
    }

    /**
     * uninstall plugin package.
     *
     *
     * @return bool
     */
    public function Uninstall()
    {
        global $db, $plugins, $cacheManager;

        $signature = $this->signature;

        // installed?
        if (!$this->AlreadyInstalled($signature)) {
            return false;
        }

        self::_Uninstall($signature); // Avoid double code

        // return
        return true;
    }

     /**
     * uninstall plugin package. (static call)
     *
     * @param string $signature Signature
     *
     * @return bool
     */
    public static function staticUninstall($signature = '')
    {
        global $db, $plugins, $cacheManager;

        // lookup
        $res = $db->Query('SELECT COUNT(*) FROM {pre}mods WHERE signature=?',
            $signature);
        list($rowCount) = $res->FetchArray(MYSQLI_NUM);
        $res->Free();

        if($rowCount <= 0) return false;

        self::_Uninstall($signature); // Avoid double code

        // return
        return true;
    }

    /**
     *
     * uninstall plugin package. (common code)
     *
     * @param string $signature Signature
     */
    private static function _Uninstall($signature): void { 
        global $db, $plugins, $cacheManager;
        // get plugins
        $packageFiles = [];
        $res = $db->Query('SELECT modname,installed,files FROM {pre}mods WHERE signature=?',
            $signature);
        while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
            if ($row['installed'] == 1) {
                $plugins->deactivatePlugin($row['modname']);
            }
            if (isset($plugins->_inactivePlugins[$row['modname']])) {
                unset($plugins->_inactivePlugins[$row['modname']]);
            }
            $packageFiles = @unserialize($row['files']);
        }
        $res->Free();

        // delete files
        if (is_array($packageFiles)) {
            foreach ($packageFiles as $file) {
                @unlink(B1GMAIL_DIR.str_replace('..', '', $file));
            }
        }

        // delete database entries
        $db->Query('DELETE FROM {pre}mods WHERE signature=?',
            $signature);

        // empty cache
        $cacheManager->Delete('dbPlugins_v2');
    }
}

/**
 * plugin interface.
 */
class BMPluginInterface
{
    public $_plugins;
    public $_inactivePlugins;
    public $_dbPlugins;
    public $_groupOptions;



    /**
     * activate a plugin.
     *
     * @param string $plugin Plugin class name
     *
     * @return bool
     */
    public function activatePlugin($plugin)
    {
        global $db, $cacheManager;

        if (!isset($this->_inactivePlugins[$plugin])) {
            return false;
        }

        $this->_plugins[$plugin] = $this->_inactivePlugins[$plugin];
        unset($this->_inactivePlugins[$plugin]);
        $this->_plugins[$plugin]['instance']->installed = true;
        $this->_plugins[$plugin]['installed'] = true;

        if ($this->_plugins[$plugin]['instance']->Install()) {
            $db->Query('UPDATE {pre}mods SET installed=1 WHERE modname=?',
                $plugin);
            if ($db->AffectedRows() == 0) {
                $db->Query('INSERT INTO {pre}mods(modname,installed) VALUES(?,1)',
                    $plugin);
            }
            $cacheManager->Delete('dbPlugins_v2');

            return true;
        } else {
            $this->_inactivePlugins[$plugin] = $this->_plugins[$plugin];
            unset($this->_plugins[$plugin]);

            return false;
        }
    }

    /**
     * pause a plugin.
     *
     * @param string $plugin Plugin class name
     *
     * @return bool
     */
    public function pausePlugin($plugin)
    {
        global $db, $cacheManager;

        if (!isset($this->_plugins[$plugin])) {
            return false;
        }

        $db->Query('UPDATE {pre}mods SET paused=1 WHERE modname=?',
                $plugin);
        $cacheManager->Delete('dbPlugins_v2');

        $this->_inactivePlugins[$plugin] = $this->_plugins[$plugin];
        unset($this->_plugins[$plugin]);
        $this->_inactivePlugins[$plugin]['paused'] = true;
        $this->_inactivePlugins[$plugin]['instance']->paused = true;

        return true;
    }

    /**
     * unpause a plugin.
     *
     * @param string $plugin Plugin class name
     *
     * @return bool
     */
    public function unpausePlugin($plugin)
    {
        global $db, $cacheManager;

        if (!isset($this->_inactivePlugins[$plugin])) {
            return false;
        }

        $db->Query('UPDATE {pre}mods SET paused=0 WHERE modname=?',
                $plugin);
        $cacheManager->Delete('dbPlugins_v2');

        $this->_plugins[$plugin] = $this->_inactivePlugins[$plugin];
        unset($this->_inactivePlugins[$plugin]);
        $this->_plugins[$plugin]['paused'] = false;
        $this->_plugins[$plugin]['instance']->paused = false;

        return true;
    }

    /**
     * deactivate a plugin.
     *
     * @param string $plugin Plugin class name
     *
     * @return bool
     */
    public function deactivatePlugin($plugin)
    {
        global $db, $cacheManager;

        if (!isset($this->_plugins[$plugin])) {
            return false;
        }

        if ($this->_plugins[$plugin]['instance']->Uninstall()) {
            $this->_inactivePlugins[$plugin] = $this->_plugins[$plugin];
            unset($this->_plugins[$plugin]);
            $this->_inactivePlugins[$plugin]['instance']->installed = false;
            $this->_inactivePlugins[$plugin]['installed'] = false;

            $db->Query('UPDATE {pre}mods SET installed=0 WHERE modname=?',
                $plugin);
            $cacheManager->Delete('dbPlugins_v2');

            return true;
        } else {
            return false;
        }
    }

    /**
     * load plugins from "plugins" directory.
     */
    public function loadPlugins(): void
    {
        global $plugins;

        $dir = B1GMAIL_DIR.'plugins/';
        $dirHandle = @dir($dir);

        if (!is_object($dirHandle)) {
            DisplayError(0x10, 'Plugin directory unavailable', 'The plugin path cannot be opened.',
                            sprintf("Path:\n%s", $dir), __FILE__, __LINE__,500);
            die();
        }

        while ($entry = $dirHandle->read()) {
            if (strtolower(substr($entry, -4)) == '.php'
                && is_file($dir.$entry)) {
                if (!include($dir.$entry)) {
                    DisplayError(0x11, 'Plugin cannot be loaded', 'A plugin cannot be loaded.',
                                    sprintf("Module:\n%s", $dir), __FILE__, __LINE__,500);
                    die();
                }
            }
        }

        $dirHandle->close();

        $this->_sortPlugins();
    }

    /**
     * sort plugins.
     */
    public function _sortPlugins(): void
    {
        uasort($this->_plugins, ['BMPluginInterface', '_pluginSort']);
        uasort($this->_inactivePlugins, ['BMPluginInterface', '_pluginSort']);
    }

    /**
     * plugin sort handler.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function _pluginSort($a, $b)
    {
        if ($a['order'] == $b['order']) {
            return strcasecmp($a['name'], $b['name']);
        }

        return $a['order'] - $b['order'];
    }

    /**
     *
     * register new plugin.
     *
     * @param string $pluginClass Plugin class name
     *
     * @return false|null
     */
    public function registerPlugin($pluginClass)
    {
        // do not load obsolete plugins (integrated in b1gMail 7.2)
        if (in_array($pluginClass, ['TabOrderPlugin', 'WidgetOrderPlugin'])) {
            return false;
        }

        $installed = false;
        $paused = false;
        
        $signature = '';
        $packageName = '';

        // installed?
        if (isset($this->_dbPlugins[$pluginClass])) {
            if ($this->_dbPlugins[$pluginClass]['installed'] == 1) {
                $installed = true;
            }
            if ($this->_dbPlugins[$pluginClass]['paused'] == 1) {
                $paused = true;
            }
            $this->_dbPlugins[$pluginClass]['pos'];
            $signature = $this->_dbPlugins[$pluginClass]['signature'];
            $packageName = $this->_dbPlugins[$pluginClass]['packageName'];
        }

        // load
        $pluginInstance = _new($pluginClass);
        $pluginInstance->internal_name = $pluginClass;
        $pluginInstance->installed = $installed;
        if ($installed && !$paused) {
            $pluginInstance->OnLoad();
        }
        $pluginInfo = [
            'type' => $pluginInstance->type,
            'name' => $pluginInstance->name,
            'description' => $pluginInstance->description,
            'version' => $pluginInstance->version,
            'author' => $pluginInstance->author,
            'id' => $pluginInstance->id,
            'order' => $pluginInstance->order,
            'instance' => $pluginInstance,
            'signature' => $signature,
            'packageName' => $packageName,
            'installed' => $installed,
            'paused' => $paused,
        ];

        // install?
        if ($installed && !$paused) {
            $this->_plugins[$pluginClass] = $pluginInfo;
        } else {
            $this->_inactivePlugins[$pluginClass] = $pluginInfo;
        }
    }

    /**
     * return widget plugins suitable for certain dashboard type.
     *
     * @param int $for Dashboard type (BMWIDGET_-constant)
     *
     * @return array
     */
    public function getWidgetsSuitableFor($for)
    {
        $result = [];

        foreach ($this->_plugins as $key => $val) {
            if ($this->_plugins[$key]['type'] == BMPLUGIN_WIDGET
                && $this->_plugins[$key]['instance']->isWidgetSuitable($for)) {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * call a plugin function.
     *
     * @param string $function    Function name
     * @param mixed  $module      "false" for all plugins or plugin name
     * @param bool   $arrayReturn Wether to return an array for multiple plugins
     *
     * @return mixed Boolean result for $module===false && $arrayReturn==false, otherwise function return value
     */
    public function callFunction($function, $module = false, $arrayReturn = false, $args = false)
    {
        if ($args === false || !is_array($args)) {
            $params = [];
        } else {
            $params = $args;
        }

        if ($module !== false
            && isset($this->_plugins[$module])) {
            if (method_exists($this->_plugins[$module]['instance'], $function)) {
                return call_user_func_array([&$this->_plugins[$module]['instance'], $function], $params);
            }
        } else {
            $retArray = [];
            foreach ($this->_plugins as $key => $val) {
                if (method_exists($this->_plugins[$key]['instance'], $function)) {
                    $retArray[$key] = call_user_func_array([&$this->_plugins[$key]['instance'], $function], $params);
                }
            }

            return $arrayReturn ? $retArray : true;
        }

        return false;
    }

    /**
     * get param of plugin.
     *
     * @param string $param  Param name
     * @param string $module Plugin name
     *
     * @return mixed
     */
    public function getParam($param, $module)
    {
        if (isset($this->_plugins[$module])) {
            return $this->_plugins[$module]['instance']->$param;
        }

        return false;
    }

    /**
     * get param of plugins.
     *
     * @param string $param Param name
     *
     * @return array
     */
    public function getParams($param)
    {
        $result = [];

        foreach ($this->_plugins as $key => $val) {
            $result[$key] = $this->_plugins[$key]['instance']->$param;
        }

        return $result;
    }

    /**
     *
     * get resource path for plugin resource.
     *
     * @param string $template Template file name
     * @param string $module   Plugin name
     * @param string $type     Type (template/css/js)
     *
     * @return false|string
     */
    public function pluginResourcePath($template, $module, $type = 'template'): string|false
    {
        global $tpl;

        if (isset($this->_plugins[$module])) {
            if ($this->_plugins[$module]['instance']->tplFromModDir) {
                return B1GMAIL_DIR.'plugins/'.($type == 'template' ? 'templates' : $type).'/'.$template;
            } else {
                return $tpl->template_dir.$template;
            }
        }

        return false;
    }



    /**
     * get group option value.
     *
     * @param string $group
     * @param string $module
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function GetGroupOptionValue($group, $module, $key, $default)
    {
        global $db;

        $value = $default;
        $res = $db->Query('SELECT value FROM {pre}groupoptions WHERE gruppe=? AND module=? AND `key`=?',
            $group,
            $module,
            $key);
        while ($row = $res->FetchArray()) {
            $value = $row['value'];
        }
        $res->Free();

        return $value;
    }

    /**
     * get all group options.
     *
     * @param int $forGroup For group?
     *
     * @return array
     */
    public function GetGroupOptions($forGroup = 0)
    {
        $result = [];

        $values = $this->getParams('_groupOptions');
        foreach ($values as $module => $value) {
            foreach ($value as $key => $info) {
                if ($forGroup != 0) {
                    $info['value'] = $this->GetGroupOptionValue($forGroup, $module, $key, $info['default']);
                }
                $info['module'] = $module;
                $info['key'] = $key;
                $result[$module.'_'.$key] = $info;
            }
        }

        return $result;
    }
}
