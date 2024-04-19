<?php
/*
 * b1gMail news plugin
 * (c) 2021 Patrick Schlangen et al
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

/**
 * News plugin.
 */
class NewsPlugin extends BMPlugin
{
    public function __construct()
    {
        // plugin info
        $this->type = BMPLUGIN_DEFAULT;
        $this->name = 'News';
        $this->author = 'b1gMail Project';
        $this->mail = 'info@b1gmail.org';
        $this->version = '1.7';
        $this->update_url = 'https://service.b1gmail.org/plugin_updates/';
        $this->website = 'https://www.b1gmail.org/';

        $this->admin_pages = true;
        $this->admin_page_title = 'News';
        $this->admin_page_icon = 'news_icon.png';
    }



    public function Install()
    {
        global $db;

        // db struct
        $databaseStructure =
              'YToxOntzOjk6ImJtNjBfbmV3cyI7YToyOntzOjY6ImZpZWxkcyI7YTo2OntpOjA7YTo2OntpOjA'
            .'7czo2OiJuZXdzaWQiO2k6MTtzOjc6ImludCgxMSkiO2k6MjtzOjI6Ik5PIjtpOjM7czozOiJQUk'
            .'kiO2k6NDtOO2k6NTtzOjE0OiJhdXRvX2luY3JlbWVudCI7fWk6MTthOjY6e2k6MDtzOjQ6ImRhd'
            .'GUiO2k6MTtzOjc6ImludCgxMSkiO2k6MjtzOjI6Ik5PIjtpOjM7czowOiIiO2k6NDtzOjE6IjAi'
            .'O2k6NTtzOjA6IiI7fWk6MjthOjY6e2k6MDtzOjU6InRpdGxlIjtpOjE7czoxMjoidmFyY2hhcig'
            .'xMjgpIjtpOjI7czoyOiJOTyI7aTozO3M6MDoiIjtpOjQ7czowOiIiO2k6NTtzOjA6IiI7fWk6Mz'
            .'thOjY6e2k6MDtzOjQ6InRleHQiO2k6MTtzOjQ6InRleHQiO2k6MjtzOjI6Ik5PIjtpOjM7czowO'
            .'iIiO2k6NDtzOjA6IiI7aTo1O3M6MDoiIjt9aTo0O2E6Njp7aTowO3M6ODoibG9nZ2VkaW4iO2k6'
            .'MTtzOjIzOiJlbnVtKCdsaScsJ25saScsJ2JvdGgnKSI7aToyO3M6MjoiTk8iO2k6MztzOjA6IiI'
            .'7aTo0O3M6NDoiYm90aCI7aTo1O3M6MDoiIjt9aTo1O2E6Njp7aTowO3M6NjoiZ3JvdXBzIjtpOj'
            .'E7czoxMToidmFyY2hhcig2NCkiO2k6MjtzOjI6Ik5PIjtpOjM7czowOiIiO2k6NDtzOjE6IioiO'
            .'2k6NTtzOjA6IiI7fX1zOjc6ImluZGV4ZXMiO2E6MTp7czo3OiJQUklNQVJZIjthOjE6e2k6MDtz'
            .'OjY6Im5ld3NpZCI7fX19fQ==';
        $databaseStructure = unserialize(base64_decode($databaseStructure));

        // sync struct
        SyncDBStruct($databaseStructure);

        // log
        PutLog(sprintf('%s v%s installed',
            $this->name,
            $this->version),
            PRIO_PLUGIN,
            __FILE__,
            __LINE__);

        return true;
    }







    public function _getNews(bool $loggedin, $groupID = 0, $sortField = 'date', $sortDirection = 'DESC'): array
    {
        global $db;

        $result = [];
        $res = $db->Query('SELECT `newsid`,`date`,`title`,`text` FROM {pre}news WHERE (`loggedin`=? OR `loggedin`=?) AND (`loggedin`=? OR `groups`=? OR `groups`=? OR `groups` LIKE ? OR `groups` LIKE ? OR `groups` LIKE ?) ORDER BY `'.$sortField.'` '.$sortDirection,
            $loggedin ? 'li' : 'nli',
            'both',
            'nli',
            '*',
            $groupID,
            $groupID.',%',
            '%,'.$groupID.',%',
            '%,'.$groupID);
        while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
            $result[$row['newsid']] = $row;
        }
        $res->Free();

        return $result;
    }
}

/**
 * News widget.
 */
class NewsWidget extends BMPlugin
{
    public function __construct()
    {
        // plugin info
        $this->type = BMPLUGIN_WIDGET;
        $this->name = 'News widget';
        $this->author = 'b1gMail Project';
        $this->mail = 'info@b1gmail.org';
        $this->version = '1.7';
        $this->widgetTemplate = 'widget.news.tpl';
        $this->widgetTitle = 'News';
        $this->update_url = 'https://service.b1gmail.org/plugin_updates/';
        $this->website = 'https://www.b1gmail.org/';
    }

    public function isWidgetSuitable($for)
    {
        return $for == BMWIDGET_START
                || $for == BMWIDGET_ORGANIZER;
    }

    /**
     * @return true
     */
    public function renderWidget(): bool
    {
        global $groupRow, $tpl;
        $tpl->assign('bmwidget_news_news', (new NewsPlugin())->_getNews(true, $groupRow['id']));

        return true;
    }
}

/*
 * register plugin + widget
 */
$plugins->registerPlugin('NewsPlugin');
$plugins->registerPlugin('NewsWidget');
