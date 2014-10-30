<?php
// Call Mail_MboxTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Mail_MboxTest::main");
}

require_once 'PHPUnit/Framework.php';

//make cvs testing work
chdir(dirname(__FILE__) . '/../');
require_once "Mail/Mbox.php";

/**
 * Test class for Mail_Mbox.
 *
 * @author Christian Weiske <cweiske@php.net>
 */
class Mail_MboxTest extends PHPUnit_Framework_TestCase
{

    protected static $file = null;
    protected static $filecopy = null;

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';
        $suite  = new PHPUnit_Framework_TestSuite('Mail_MboxTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        chdir(dirname(__FILE__) . '/../');
        Mail_MboxTest::$file     = dirname(__FILE__) . '/testbox';
        Mail_MboxTest::$filecopy = tempnam('/tmp', 'Mail_MboxTestcopy');

        $this->mbox = new Mail_Mbox(Mail_MboxTest::$file);
        $this->assertTrue($this->mbox->open());
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
        $this->mbox->close();
        //remove the tmp file
        unlink(Mail_MboxTest::$filecopy);
    }

    /**
     * opens the file
     */
    public function testOpen() {
        $this->assertEquals(11, $this->mbox->size());
    }

    /**
     * closes the file pointer
     */
    public function testClose() {
        $this->assertTrue($this->mbox->close());
        $this->assertType('PEAR_Error', $this->mbox->close());
    }

    /**
     * returns the number of messages
     */
    public function testSize() {
        $this->assertEquals(11, $this->mbox->size());
    }

    /**
     * Returns a message
     */
    public function testGet() {
        $msg = $this->mbox->get(0);
        $this->assertContains('My own hands[TM]', $msg);

        $msg = $this->mbox->get(2);
        $this->assertContains('somebody@yandex.ru', $msg);

        $mbox2 = new Mail_Mbox(Mail_MboxTest::$file);
        $this->assertType('PEAR_Error', $mbox2->get(0));
    }

    /**
     * Removes a message
     */
    public function testRemove() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $this->assertTrue($mbox->remove(0));
        $this->assertEquals(10, $mbox->size());
        //Shouldn't exist any more
        $this->assertNotContains('My own hands[TM]', $mbox->get(0));
        $this->assertContains('neXus MIME Mail', $mbox->get(0));

        $this->assertTrue($mbox->remove(
            array(0, 1, 2, 3, 4, 5, 9)
        ));
        $this->assertEquals(3, $mbox->size());
        $this->assertContains('CME-V6.5.4.3', $mbox->get(0));

        $this->assertTrue($mbox->close());
    }

    /**
     * Update a message
     */
    public function testUpdate() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $this->assertEquals(11, $mbox->size());

        $this->assertNotContains('Hoppla', $mbox->get(0));
        $this->assertTrue(
            $mbox->update(0, $mbox->get(0) . 'Hoppla')
        );
        $this->assertContains('Hoppla', $mbox->get(0));

        $this->assertTrue($mbox->close());
    }

    /**
     * Insert a new email
     */
    public function testInsert() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $this->assertEquals(11, $mbox->size());

        //insert at the end
        $this->assertTrue($mbox->insert(
            $mbox->get(0)
        ));
        $this->assertEquals(12, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(11));

        //insert at the end
        $this->assertTrue($mbox->insert(
            $mbox->get(0), -1
        ));
        $this->assertEquals(13, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(12));

        //insert at the end
        $this->assertTrue($mbox->insert(
            $mbox->get(0), null
        ));
        $this->assertEquals(14, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(13));

        //insert after first message
        $this->assertNotContains('My own hands', $mbox->get(1));
        $this->assertTrue($mbox->insert(
            $mbox->get(0), 1
        ));
        $this->assertEquals(15, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(1));

        $this->assertTrue($mbox->close());
    }

    /**
     * Insert and invalid message
     */
    public function testInsertInvalid() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $err = $mbox->insert('this is not valid', 0);
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MSG_INVALID, $err->getCode());
    }

    /**
     * Append an email to the end of the thingy
     */
    public function testAppend() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());
        $this->assertEquals(11, $mbox->size());

        $this->assertTrue($mbox->append(
            $mbox->get(0)
        ));
        $this->assertEquals(12, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(11));

        $this->assertTrue($mbox->append(
            $mbox->get(0)
        ));
        $this->assertTrue($mbox->append(
            $mbox->get(0)
        ));
        $this->assertTrue($mbox->append(
            $mbox->get(0)
        ));

        $this->assertEquals(15, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(12));
        $this->assertContains('My own hands', $mbox->get(13));
        $this->assertContains('My own hands', $mbox->get(14));
    }

    /**
     * Append an invalid message
     */
    public function testAppendInvalid() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $err = $mbox->append('this is invalid');
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MSG_INVALID, $err->getCode());
    }

    /**
     * Append an email to the end of the thingy
     * without using auto-reopen.
     */
    public function testAppendNoReopen() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $mbox->setAutoReopen(false);

        $this->assertTrue($mbox->open());
        $this->assertEquals(11, $mbox->size());

        $zero = $mbox->get(0);

        $this->assertTrue($mbox->append(
            $zero
        ));
        //should still be 11, since not reloaded
        $this->assertEquals(11, $mbox->size());

        $this->assertTrue($mbox->open());
        $this->assertEquals(12, $mbox->size());
        $this->assertContains('My own hands', $mbox->get(11));

        $this->assertTrue($mbox->append(
            $zero
        ));
        $this->assertTrue($mbox->append(
            $zero
        ));
        $this->assertTrue($mbox->append(
            $zero
        ));

        //still 12
        $this->assertEquals(12, $mbox->size());

        $this->assertTrue($mbox->open());
        $this->assertEquals(15, $mbox->size());

        $this->assertContains('My own hands', $mbox->get(12));
        $this->assertContains('My own hands', $mbox->get(13));
        $this->assertContains('My own hands', $mbox->get(14));
    }

    /**
     * Update to an invalid message
     */
    public function testUpdateInvalid() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $err = $mbox->update(0, 'this is invalid');
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MSG_INVALID, $err->getCode());
    }

    /**
     * Moves a file
     */
    public function test_move() {
        $file = tempnam('/tmp', 'Mail_MboxTest');
        $file2 = $file . 'second';

        $this->assertTrue(file_exists($file));
        $this->assertFalse(file_exists($file2));

        $mbox = new Mail_Mbox($file);
        $this->assertTrue(file_exists($file));
        $this->assertEquals(0, $mbox->size());
        $this->assertTrue($mbox->_move($file, $file2));
        $this->assertTrue(file_exists($file2));
        $this->assertFalse(file_exists($file));

        $this->assertEquals(0, $mbox->size());

        //remove the tmp file
        unlink($file2);
    }

    /**
     * Checks if a file has been modified
     */
    public function testHasBeenModified() {
        $this->copy();
        $mbox = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox->open());

        $this->assertFalse($mbox->hasBeenModified());
        //get a new timestamp for the change
        sleep(1);

        $mbox2 = new Mail_Mbox(Mail_MboxTest::$filecopy);
        $this->assertTrue($mbox2->open());
        $this->assertTrue($mbox2->remove(0));
        $this->assertTrue($mbox2->close());

        $this->assertTrue($mbox->hasBeenModified());

        //This methods should not allow modifying a changed file.
        $err = $mbox->remove(0);
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MODIFIED, $err->getCode());

        $err = $mbox->insert('From Test');
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MODIFIED, $err->getCode());

        $err = $mbox->update(0, 'From Test');
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(MAIL_MBOX_ERROR_MODIFIED, $err->getCode());

        $this->assertTrue($mbox->close());
    }

    public function testSetTmpDir()
    {
        $this->assertEquals('/tmp', $this->mbox->tmpdir);
        $this->assertTrue($this->mbox->setTmpDir('/this/is/a/tmp/dir'));
        $this->assertEquals('/this/is/a/tmp/dir', $this->mbox->tmpdir);

        $this->assertTrue($this->mbox->setTmpDir('/tmp'));
        $this->assertEquals('/tmp', $this->mbox->tmpdir);
    }

    public function testGetTmpDir()
    {
        $this->assertEquals('/tmp', $this->mbox->tmpdir);
        $this->assertEquals('/tmp', $this->mbox->getTmpDir());
        $this->assertTrue($this->mbox->setTmpDir('/this/is/a/tmp/dir'));
        $this->assertEquals('/this/is/a/tmp/dir', $this->mbox->tmpdir);
        $this->assertEquals('/this/is/a/tmp/dir', $this->mbox->getTmpDir());
    }

    public function testSetDebug()
    {
        $this->assertFalse($this->mbox->debug);
        $this->mbox->setDebug(true);
        $this->assertTrue($this->mbox->debug);
        $this->mbox->setDebug(false);
        $this->assertFalse($this->mbox->debug);
    }

    public function testGetDebug()
    {
        $this->assertFalse($this->mbox->debug);
        $this->assertFalse($this->mbox->getDebug());

        $this->mbox->setDebug(true);
        $this->assertTrue($this->mbox->debug);
        $this->assertTrue($this->mbox->getDebug());

        $this->mbox->setDebug(false);
        $this->assertFalse($this->mbox->debug);
        $this->assertFalse($this->mbox->getDebug());
    }

    /**
     * Test message escaping
     *
     * @return void
     */
    public function test_escapeMessage()
    {
        $this->assertEquals(
            <<<MBX
From someone.who@loves.you
Subject: test

>From now on, no more bugs!
>>From what I said...
>>>From where are you coming?


MBX
            , $this->mbox->_escapeMessage(
                <<<MBX
From someone.who@loves.you
Subject: test

From now on, no more bugs!
>From what I said...
>>From where are you coming?

MBX
            )
        );
    }

    /**
     * Test message escaping with "From " midst of the text
     *
     * @return void
     */
    public function test_escapeMessageFromMid()
    {
        $this->assertEquals(
            <<<MBX
From someone.who@loves.you
Subject: test

>From now on, no more bugs!
>>From what I said...
>>>From where are you coming?
>From From From From what?


MBX
            , $this->mbox->_escapeMessage(
                <<<MBX
From someone.who@loves.you
Subject: test

From now on, no more bugs!
>From what I said...
>>From where are you coming?
From From From From what?

MBX
            )
        );
    }

    /**
     * Test message unescaping with ">From " midst of the text
     *
     * @return void
     */
    public function test_unescapeMessageFromMid()
    {
        $this->assertEquals(
            <<<MBX
From someone.who@loves.you
Subject: test

From now on, no more bugs!
>From what I said...
>>From where are you coming?
From >From >From >From what?

MBX
            , $this->mbox->_unescapeMessage(
                <<<MBX
From someone.who@loves.you
Subject: test

>From now on, no more bugs!
>>From what I said...
>>>From where are you coming?
>From >From >From >From what?


MBX
            )
        );
    }

    /**
     * Opening a non-existing mbox file does not succeed, and
     * there was no way to create one.
     * With bug #16487, open() accepts a $create parameter now.
     *
     * @link http://pear.php.net/bugs/bug.php?id=16487
     *
     * @return void
     */
    public function testBug16487()
    {
        //file does not exist yet
        $file = tempnam(sys_get_temp_dir(), 'mail_mbox');
        unlink($file);
        $mbox = new Mail_Mbox($file);
        //open without parameter does not create anything
        $err = $mbox->open();
        $this->assertType('PEAR_Error', $err);
        $this->assertEquals(
            MAIL_MBOX_ERROR_FILE_NOT_EXISTING, $err->getCode()
        );

        //with true as first parameter the file gets created
        $err = $mbox->open(true);
        $this->assertTrue($err);
        $mbox->insert('From someone who loves you');
        $mbox->close();

        $this->assertFileExists($file);
        unlink($file);
    }

    /**
     * While the bug was incorrect, it showed that we do not
     * escape messages properly.
     * Here we test that escaped messages are stored and read
     * properly.
     *
     * @link http://pear.php.net/bugs/bug.php?id=16758
     *
     * @return void
     */
    public function testBug16758()
    {
        $msg =<<<MBX
From someone.who@loves.you
Subject: test

From now on, no more bugs!
>From what I said...
>>From where are you coming?

MBX;
        //file does not exist yet
        $mbox = new Mail_Mbox(self::$filecopy);
        $this->assertTrue($mbox->open());
        $this->assertTrue($mbox->append($msg));
        $mbox->close();

        $mbox = new Mail_Mbox(self::$filecopy);
        $this->assertTrue($mbox->open());
        $this->assertEquals(1, $mbox->size());
        $this->assertEquals($msg, $mbox->get(0));
    }//public function testBug16758()



    /**
     * Helper method to copy $file to $filecopy
     */
    protected function copy()
    {
        copy(Mail_MboxTest::$file, Mail_MboxTest::$filecopy);
    }

}

// Call Mail_MboxTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Mail_MboxTest::main") {
    Mail_MboxTest::main();
}
?>
