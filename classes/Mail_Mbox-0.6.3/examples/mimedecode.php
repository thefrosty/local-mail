<?php
//read the contents of the demo mailbox and
//use the Mail_Mime package to parse the mails

require_once 'Mail/Mbox.php';
require_once "Mail/mimeDecode.php";

//reads a mbox file
$file = dirname(__FILE__) . '/demobox';
echo 'Using file ' . $file . "\n";

$mbox = new Mail_Mbox($file);
$mbox->open();

for ($n = 0; $n < $mbox->size(); $n++) {
    $message = $mbox->get($n);

    $decode = new Mail_mimeDecode($message, "\r\n");
    $structure = $decode->decode();

    echo 'Mail #' . $n . "\n";
//    print_r($structure);
    echo 'Subject: ' . $structure->headers['subject'] . "\n";
    echo 'From:    ' . $structure->headers['from'] . "\n";
    echo "\n";
}

$mbox->close();
?>