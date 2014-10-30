<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Mail_Mbox_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'Mail_MboxTest.php';


class Mail_Mbox_AllTests
{
    public static function main()
    {

        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Mail_Mbox test suite');
        $suite->addTestSuite('Mail_MboxTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Mail_Mbox_AllTests::main') {
    Mail_Mbox_AllTests::main();
}
?>