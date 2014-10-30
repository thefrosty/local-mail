<?php
//read the subjects of the demo mailbox

require_once 'Mail/Mbox.php';

//reads a mbox file
$file = dirname(__FILE__) . '/demobox';
echo 'Using file ' . $file . "\n";

$mbox = new Mail_Mbox($file);
$mbox->open();

for ($n = 0; $n < $mbox->size(); $n++) {
    $message = $mbox->get($n);
    preg_match('/Subject: (.*)$/m', $message, $matches);
    $subject = $matches[1];
    echo 'Mail #' . $n . ': ' . $subject . "\n";
}

$mbox->close();
?>