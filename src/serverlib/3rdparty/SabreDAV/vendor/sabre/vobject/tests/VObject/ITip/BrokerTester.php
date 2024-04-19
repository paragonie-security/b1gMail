<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Reader;

/**
 * Utilities for testing the broker
 * 
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/) 
 * @license http://sabre.io/license/ Modified BSD License
 */
abstract class BrokerTester extends \Sabre\VObject\TestCase {

    function parse($oldMessage, $newMessage, $expected = array(), $currentUser = 'mailto:one@example.org'): void {

        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, $currentUser, $oldMessage);

        $this->assertEquals(count($expected), count($result));

        foreach($expected as $index=>$ex) {

            $message = $result[$index];

            foreach($ex as $key=>$val) {

                if ($key==='message') {
                    $this->assertVObjEquals(
                        $val,
                        $message->message->serialize()
                    );
                } else {
                    $this->assertEquals($val, $message->$key);
                }

            }

        }

    }


}
