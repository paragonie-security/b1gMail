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

if(!defined('B1GMAIL_INIT'))
	die('Directly calling this file is not supported');

if(!class_exists('BMMailbox'))
	include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');
if(!class_exists('BMAddressbook'))
	include(B1GMAIL_DIR . 'serverlib/addressbook.class.php');
if(!class_exists('BMMailBuilder'))
	include(B1GMAIL_DIR . 'serverlib/mailbuilder.class.php');
if(!class_exists('BMWebdisk'))
	include(B1GMAIL_DIR . 'serverlib/webdisk.class.php');

/**
 * interface for b1gMail tools (like mailchecker)
 *
 */
class BMToolInterface
{
	var $_sms, $_user, $_group;

































	/**
	 *
	 * check user login, return user id on success, 0 otherwise
	 *
	 * @param string $userName User E-Mail
	 * @param string $passwordHash Password hash (MD5)
	 *
	 * @return (array[]|bool|int|mixed|string)[]
	 *
	 * @psalm-return array{loginOK: 0|1, userID?: int, hostName?: mixed, pop3Access?: bool, imapAccess?: bool, smtpAccess?: bool, smsAccess?: bool, webdiskAccess?: bool, balance?: mixed, latestVersion?: string, notificationInterval?: 0|mixed, plugins?: array<array>, webdiskSpaceLimit?: mixed, webdiskUsedSpace?: mixed, webdiskTrafficLimit?: mixed, webdiskUsedTraffic?: mixed, webdiskMaxFileSize?: mixed, smsMaxChars?: mixed, smsTypes?: mixed, smsPre?: '0'|mixed, smsOwnFrom?: bool, smsFrom?: mixed}
	 */
	function CheckLogin($userName, $passwordHash): array
	{
		global $db, $bm_prefs, $plugins;

		$userName = EncodeEMail($userName);
		$userID = BMUser::GetID($userName);
		if($userID != 0)
		{
			$res = $db->Query('SELECT passwort,passwort_salt,gesperrt,gruppe,mail2sms_nummer FROM {pre}users WHERE id=?',
				$userID);
			if($res->RowCount() == 1)
			{
				$row = $res->FetchArray();

				$user = _new('BMUser', array($userID));

				if(strtolower($row['passwort']) === strtolower(md5($passwordHash.$row['passwort_salt']))
					&& $row['gesperrt'] == 'no')
				{
					$group = $user->GetGroup();
					$groupRow = $group->_row;
					$this->_group = $group;

					// get latest toolbox version no
					$latestVersion = '0.0.0';
					$verRes = $db->Query('SELECT `versionid`,`base_version` FROM {pre}tbx_versions WHERE `status`=? ORDER BY `versionid` DESC LIMIT 1',
						'released');
					while($verRow = $verRes->FetchArray(MYSQLI_ASSOC))
						$latestVersion = sprintf('%s.%d', $verRow['base_version'], $verRow['versionid']);
					$verRes->Free();

					$result = array('loginOK' => 1, 'userID' => $userID,
						'hostName' => $bm_prefs['b1gmta_host'],
						'pop3Access' => $groupRow['pop3'] == 'yes',
						'imapAccess' => $groupRow['imap'] == 'yes',
						'smtpAccess' => $groupRow['smtp'] == 'yes',
						'smsAccess' => $user->SMSEnabled() && $groupRow['tbx_smsmanager'] == 'yes',
						'webdiskAccess' => $groupRow['webdisk'] + $user->_row['diskspace_add'] > 0 && $groupRow['tbx_webdisk'] == 'yes',
						'balance' => $user->GetBalance(),
						'latestVersion' => $latestVersion,
						'notificationInterval' => $groupRow['notifications'] == 'yes' ? $bm_prefs['notify_interval'] : 0,
						'plugins' => array());

					if($result['webdiskAccess'])
					{
						$result['webdiskSpaceLimit'] = $groupRow['webdisk'] + $user->_row['diskspace_add'];
						$result['webdiskUsedSpace'] = $user->_row['diskspace_used'];
						$result['webdiskTrafficLimit'] = $groupRow['traffic'] + $user->_row['traffic_add'];
						$result['webdiskUsedTraffic'] = $user->_row['traffic_down']+$user->_row['traffic_up'];
						$result['webdiskMaxFileSize'] = ParsePHPSize(ini_get('upload_max_filesize'));
					}

					$moduleResult = $plugins->callFunction('ToolInterfaceCheckLogin', false, true, array(&$user));
					foreach($moduleResult as $pluginName=>$addInfo)
						if(is_array($addInfo) && count($addInfo) > 0)
							$result['plugins'][$pluginName] = $addInfo;

					if($result['smsAccess'])
					{
						$sms = _new('BMSMS', array($userID, &$user));
						$result['smsMaxChars'] = $sms->GetMaxChars();
						$result['smsTypes'] = $sms->GetTypes();
						$result['smsPre'] = trim($groupRow['sms_pre']) == ''
							? '0'
							: $groupRow['sms_pre'];

						if($groupRow['sms_ownfrom'] == 'yes')
						{
							$result['smsOwnFrom'] = true;
							$result['smsFrom'] = $row['mail2sms_nummer'];
						}
						else
						{
							$result['smsOwnFrom'] = false;
							$result['smsFrom'] = $groupRow['sms_from'];
						}

						$this->_sms = $sms;
					}

					$this->_user = $user;
					return($result);
				}
			}
			$res->Free();
		}
		return(array('loginOK' => 0));
	}

	function HandleNonexistentMethod($method, $params, &$result): void
	{
		ModuleFunction('ToolInterfaceHandler', array($method, $params, &$result, &$this));
	}
}
