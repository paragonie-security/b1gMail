<?php

namespace Sabre\CardDAV;

use PDO;

class TestUtil {

    static function getBackend(): Backend\PDO {

        $backend = new Backend\PDO(self::getSQLiteDB());
        return $backend;

    }

    static function getSQLiteDB(): PDO {

        if (file_exists(SABRE_TEMPDIR . '/testdb.sqlite'))
            unlink(SABRE_TEMPDIR . '/testdb.sqlite');

        $pdo = new PDO('sqlite:' . SABRE_TEMPDIR . '/testdb.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        // Yup this is definitely not 'fool proof', but good enough for now.
        $queries = explode(';', file_get_contents(__DIR__ . '/../../../examples/sql/sqlite.addressbooks.sql'));
        foreach($queries as $query) {
            $pdo->exec($query);
        }
        // Inserting events through a backend class.
        $backend = new Backend\PDO($pdo);
        $backend->createAddressBook(
            'principals/user1',
            'UUID-123467',
            array(
                '{DAV:}displayname' => 'user1 addressbook',
                '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
            )
        );
        $backend->createAddressBook(
            'principals/user1',
            'UUID-123468',
            array(
                '{DAV:}displayname' => 'user1 addressbook2',
                '{urn:ietf:params:xml:ns:carddav}addressbook-description' => 'AddressBook description',
            )
        );
        $backend->createCard($addressbookId, 'UUID-2345', self::getTestCardData());
        return $pdo;

    }

    static function getTestCardData($type = 1): string {

        $addressbookData = 'BEGIN:VCARD
VERSION:3.0
PRODID:-//Acme Inc.//RoadRunner 1.0//EN
FN:Wile E. Coyote
N:Coyote;Wile;Erroll;;
ORG:Acme Inc.
UID:39A6B5ED-DD51-4AFE-A683-C35EE3749627
REV:2012-06-20T07:00:39+00:00
END:VCARD';

        return $addressbookData;

    }

}
