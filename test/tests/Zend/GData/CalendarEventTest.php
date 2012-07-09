<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_GData
 */

namespace ZendTest\GData;

use Zend\GData\Calendar;
use Zend\GData\App\Extension;

/**
 * @category   Zend
 * @package    Zend_GData_Calendar
 * @subpackage UnitTests
 * @group      Zend_GData
 * @group      Zend_GData_Calendar
 */
class CalendarEventTest extends \PHPUnit_Framework_TestCase
{
    protected $eventFeed = null;

    /**
      * Called before each test to setup any fixtures.
      */
    public function setUp()
    {
        $eventFeedText = file_get_contents(
                'Zend/GData/Calendar/_files/TestDataEventFeedSample1.xml',
                true);
        $this->eventFeed = new Calendar\EventFeed($eventFeedText);
    }

    /**
      * Verify that a given property is set to a specific value
      * and that the getter and magic variable return the same value.
      *
      * @param object $obj The object to be interrogated.
      * @param string $name The name of the property to be verified.
      * @param object $value The expected value of the property.
      */
    protected function verifyProperty($obj, $name, $value)
    {
        $propName = $name;
        $propGetter = "get" . ucfirst($name);

        $this->assertEquals($obj->$propGetter(), $obj->$propName);
        $this->assertEquals($value, $obj->$propGetter());
    }

    /**
      * Verify that a given property is set to a specific value
      * and that the getter and magic variable return the same value.
      *
      * @param object $obj The object to be interrogated.
      * @param string $name The name of the property to be verified.
      * @param string $secondName 2nd level accessor function name
      * @param object $value The expected value of the property.
      */
    protected function verifyProperty2($obj, $name, $secondName, $value)
    {
        $propName = $name;
        $propGetter = "get" . ucfirst($name);
        $secondGetter = "get" . ucfirst($secondName);

        $this->assertEquals($obj->$propGetter(), $obj->$propName);
        $this->assertEquals($value, $obj->$propGetter()->$secondGetter());
    }

    /**
      * Convert sample feed to XML then back to objects. Ensure that
      * all objects are instances of EventEntry and object count matches.
      */
    public function testEventFeedToAndFromString()
    {
        $entryCount = 0;
        foreach ($this->eventFeed as $entry) {
            $entryCount++;
            $this->assertTrue($entry instanceof Calendar\EventEntry);
        }
        $this->assertTrue($entryCount > 0);

        /* Grab XML from $this->eventFeed and convert back to objects */
        $newEventFeed = new Calendar\EventFeed(
                $this->eventFeed->saveXML());
        $newEntryCount = 0;
        foreach ($newEventFeed as $entry) {
            $newEntryCount++;
            $this->assertTrue($entry instanceof Calendar\EventEntry);
        }
        $this->assertEquals($entryCount, $newEntryCount);
    }

    /**
      * Ensure that there number of lsit feeds equals the number
      * of calendars defined in the sample file.
      */
    public function testEntryCount()
    {
        //TODO feeds implementing ArrayAccess would be helpful here
        $entryCount = 0;
        foreach ($this->eventFeed as $entry) {
            $entryCount++;
        }
        $this->assertEquals(10, $entryCount);
    }

    /**
      * Check for the existence of an <atom:author> and verify that they
      * contain the expected values.
      */
    public function testAuthor()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's author is correct
        $feedAuthor = $feed->getAuthor();
        $this->assertEquals($feedAuthor, $feed->author);
        $this->assertEquals(1, count($feedAuthor));
        $this->assertTrue($feedAuthor[0] instanceof Extension\Author);
        $this->verifyProperty2($feedAuthor[0], "name", "text", "GData Ops Demo");
        $this->verifyProperty2($feedAuthor[0], "email", "text", "gdata.ops.demo@gmail.com");
        $this->assertTrue($feedAuthor[0]->getUri() instanceof Extension\Uri);
        $this->verifyProperty2($feedAuthor[0], "uri", "text", "http://test.address.invalid/");

        // Assert that each entry has valid author data
        foreach ($feed as $entry) {
            $entryAuthor = $entry->getAuthor();
            $this->assertEquals(1, count($entryAuthor));
            $this->verifyProperty2($entryAuthor[0], "name", "text", "GData Ops Demo");
            $this->verifyProperty2($entryAuthor[0], "email", "text", "gdata.ops.demo@gmail.com");
            $this->verifyProperty($entryAuthor[0], "uri", null);
        }
    }

    /**
      * Check for the existence of an <atom:id> and verify that it contains
      * the expected value.
      */
    public function testId()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's ID is correct
        $this->assertTrue($feed->getId() instanceof Extension\Id);
        $this->verifyProperty2($feed, "id", "text",
                "http://www.google.com/calendar/feeds/default/private/full");

        // Assert that all entry's have an Atom ID object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getId() instanceof Extension\Id);
        }

        // Assert one of the entry's IDs
        $entry = $feed[1];
        $this->verifyProperty2($entry, "id", "text",
                "http://www.google.com/calendar/feeds/default/private/full/2qt3ao5hbaq7m9igr5ak9esjo0");
    }

    /**
      * Check for the existence of an <atom:published> and verify that it contains
      * the expected value.
      */
    public function testPublished()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have an Atom Published object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getPublished() instanceof Extension\Published);
        }

        // Assert one of the entry's Published dates
        $entry = $feed[1];
        $this->verifyProperty2($entry, "published", "text", "2007-03-20T21:26:04.000Z");
    }

    /**
      * Check for the existence of an <atom:published> and verify that it contains
      * the expected value.
      */
    public function testUpdated()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's updated date is correct
        $this->assertTrue($feed->getUpdated() instanceof Extension\Updated);
        $this->verifyProperty2($feed, "updated", "text",
                "2007-03-20T21:29:57.000Z");

        // Assert that all entry's have an Atom Published object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getUpdated() instanceof Extension\Updated);
        }

        // Assert one of the entry's Published dates
        $entry = $feed[1];
        $this->verifyProperty2($entry, "updated", "text", "2007-03-20T21:28:46.000Z");
    }

    /**
      * Check for the existence of an <atom:title> and verify that it contains
      * the expected value.
      */
    public function testTitle()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's title is correct
        $this->assertTrue($feed->getTitle() instanceof Extension\Title);
        $this->verifyProperty2($feed, "title", "text",
                "GData Ops Demo");

        // Assert that all entry's have an Atom ID object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getTitle() instanceof Extension\Title);
        }

        // Assert one of the entry's Titles
        $entry = $feed[1];
        $this->verifyProperty2($entry, "title", "text", "Afternoon at Dolores Park with Kim");
    }

    /**
      * Check for the existence of an <atom:subtitle> and verify that it contains
      * the expected value.
      */
    public function testSubtitle()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's title is correct
        $this->assertTrue($feed->getSubtitle() instanceof Extension\Subtitle);
        $this->verifyProperty2($feed, "subtitle", "text",
                "Demo Feed");
    }

    /**
      * Check for the existence of an <gCal:timezone> and verify that it contains
      * the expected value.
      */
    public function testTimezone()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's timezone is correct
        $this->assertTrue($feed->getTimezone() instanceof \Zend\GData\Calendar\Extension\Timezone);
        $this->verifyProperty2($feed, "timezone", "value",
                "America/Los_Angeles");
    }

    /**
      * Check for the existence of an <openSearch:startIndex> and verify that it contains
      * the expected value.
      */
    public function testStartIndex()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's startIndex is correct
        $this->assertTrue($feed->getStartIndex() instanceof \Zend\GData\Extension\OpenSearchStartIndex);
        $this->verifyProperty2($feed, "startIndex", "text", "1");
    }

    /**
      * Check for the existence of an <openSearch:itemsPerPage> and verify that it contains
      * the expected value.
      */
    public function testItemsPerPage()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's itemsPerPage is correct
        $this->assertTrue($feed->getItemsPerPage() instanceof \Zend\GData\Extension\OpenSearchItemsPerPage);
        $this->verifyProperty2($feed, "itemsPerPage", "text", "25");
    }

    /**
      * Check for the existence of an <atom:content> and verify that it contains
      * the expected value.
      */
    public function testContent()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have a content object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getContent() instanceof Extension\Content);
        }

        // Assert one of the entry's values
        $entry = $feed[1];
        $this->verifyProperty2($entry, "content", "text", "This will be fun.");
    }

    /**
      * Check for the existence of an <gCal:sendEventNotifications> and verify that it contains
      * the expected value.
      */
    public function testSendEventNotifications()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have a sendEventNotifications object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getSendEventNotifications() instanceof \Zend\GData\Calendar\Extension\SendEventNotifications);
        }

        // Assert one of the entry's values
        $entry = $feed[1];
        $this->verifyProperty2($entry, "sendEventNotifications", "value", false);
    }

    /**
      * Check for the existence of an <gd:eventStatus> and verify that it contains
      * the expected value.
      */
    public function testEventStatus()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have a eventStatus object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getEventStatus() instanceof \Zend\GData\Extension\EventStatus);
        }

        // Assert one of the entry's values
        $entry = $feed[1];
        $this->verifyProperty2($entry, "eventStatus", "value", "http://schemas.google.com/g/2005#event.confirmed");
    }

    /**
      * Check for the existence of an <gd:comments> and verify that it contains
      * the expected value.
      */
    public function testComments()
    {
        $feed = $this->eventFeed;

        // Assert one of the entry's commments links
        $entry = $feed[1];
        $this->assertTrue($entry->getComments() instanceof \Zend\GData\Extension\Comments);
        $this->verifyProperty2($entry->getComments(), "feedLink", "href", "http://www.google.com/calendar/feeds/default/private/full/2qt3ao5hbaq7m9igr5ak9esjo0/comments");
    }

    /**
      * Check for the existence of an <gCal:visibility> and verify that it contains
      * the expected value.
      */
    public function testVisibility()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have a visibility object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getVisibility() instanceof \Zend\GData\Extension\Visibility);
        }

        // Assert one of the entries values
        $entry = $feed[1];
        $this->verifyProperty2($entry, "visibility", "value", "http://schemas.google.com/g/2005#event.private");
    }

    /**
      * Check for the existence of an <gCal:transparency> and verify that it contains
      * the expected value.
      */
    public function testTransparency()
    {
        $feed = $this->eventFeed;

        // Assert that all entry's have a transparency object
        foreach ($feed as $entry) {
            $this->assertTrue($entry->getTransparency() instanceof \Zend\GData\Extension\Transparency);
        }

        // Assert one of the entries values
        $entry = $feed[1];
        $this->verifyProperty2($entry, "transparency", "value", "http://schemas.google.com/g/2005#event.opaque");
    }

    /**
      * Check for the existence of an <gd:when> and verify that it contains
      * the expected value.
      */
    public function testWhen()
    {
        $feed = $this->eventFeed;

        // Assert one of the entry's values
        $entry = $feed[1];
        $when = $entry->getWhen();
        $this->assertEquals($entry->getWhen(), $entry->when);
        $this->assertEquals(1, count($when));
        $w = $when[0];
        $this->assertTrue($w instanceof \Zend\GData\Extension\When);
        $this->verifyProperty($w, "startTime", "2007-03-24T12:00:00.000-07:00");
        $this->verifyProperty($w, "endTime", "2007-03-24T15:00:00.000-07:00");

        // Assert that the associated reminders are correct
        $reminders = $w->getReminders();
        $this->assertEquals(1, count($reminders));
        $this->verifyProperty($reminders[0], "minutes", "20");
        $this->verifyProperty($reminders[0], "method", "alert");
    }

    /**
      * Check for the existence of an <gd:where> and verify that it contains
      * the expected value.
      */
    public function testWhere()
    {
        $feed = $this->eventFeed;

        // Assert one of the entry's values
        $entry = $feed[1];
        $where = $entry->getWhere();
        $this->assertEquals(1, count($where));
        $this->assertTrue($where[0] instanceof \Zend\GData\Extension\Where);
        $this->verifyProperty($where[0], "valueString", "Dolores Park with Kim");
    }

    /**
      * Check for the existence of an <gd:where> and verify that it contains
      * the expected value.
      */
    public function testWho()
    {
        $feed = $this->eventFeed;

        // For one of the entries, make sure that all who entries are of the
        // right kind
        $entry = $feed[1];
        $who = $entry->getWho();
        foreach ($who as $w) {
            $this->assertTrue($w instanceof \Zend\GData\Extension\Who);
        }
        $this->assertEquals(2, count($who));

        // Check one of the who entries to make sure the values are valid
        $this->verifyProperty($who[0], "rel", "http://schemas.google.com/g/2005#event.organizer");
        $this->verifyProperty($who[0], "valueString", "GData Ops Demo");
        $this->verifyProperty($who[0], "email", "gdata.ops.demo@gmail.com");
        $this->verifyProperty2($who[0], "attendeeStatus", "value", "http://schemas.google.com/g/2005#event.accepted");
    }

    /**
      * Check for the existence of an <atom:generator> and verify that it contains
      * the expected value.
      */
    public function testGenerator()
    {
        $feed = $this->eventFeed;

        // Assert that the feed's generator is correct
        $this->assertTrue($feed->getGenerator() instanceof Extension\Generator);
        $this->verifyProperty2($feed, "generator", "version", "1.0");
        $this->verifyProperty2($feed, "generator", "uri", "http://www.google.com/calendar");
        $this->verifyProperty2($feed, "generator", "text", "Google Calendar");
    }

    /**
      * Check for the existence of an <gd:quickadd> and verify that it contains
      * the expected value.
      */
    public function testQuickAdd()
    {
        $feed = $this->eventFeed;

        // Assert that one of the event's QuickAdd entries is correct
        $quickAdd = $feed->entry[1]->getQuickAdd();
        $this->assertTrue($quickAdd instanceof \Zend\GData\Calendar\Extension\QuickAdd);
        $this->verifyProperty($quickAdd, "value", true);
    }

}
