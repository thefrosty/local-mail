<?php
//modify an mbox file
require_once 'Mail/Mbox.php';

//This function just lists all subjects
function listSubjects($mbox) {
    echo 'Mbox has ' . $mbox->size() . ' messages.' . "\n";

    for ($n = 0; $n < $mbox->size(); $n++) {
        $message = $mbox->get($n);
        preg_match('/Subject: (.*)$/m', $message, $matches);
        $subject = $matches[1];
        echo 'Mail #' . $n . ': ' . $subject . "\n";
    }
    echo "\n";
}


//make a copy of the demo file
$original = dirname(__FILE__) . '/demobox';
$file = tempnam('/tmp', 'mbox-copy-');
copy($original, $file);
echo 'Using file ' . $file . "\n";

$mbox = new Mail_Mbox($file);
$mbox->open();
listSubjects($mbox);


echo 'append a message to the end of the box' . "\n";
$message = $mbox->get(0) . "\n" . 'This is a copy of the mail';
$mbox->insert($message);
listSubjects($mbox);


echo 'insert a message before the second message' . "\n";
$message = $mbox->get(0) . "\n" . 'This is another copy of the mail';
$mbox->insert($message, 1);
listSubjects($mbox);


echo 'remove the last message' . "\n";
$mbox->remove(
    $mbox->size() - 1
);
listSubjects($mbox);


echo 'remove the first two messages' . "\n";
$mbox->remove(array(0, 1));
listSubjects($mbox);


$mbox->close();

//remove the tmp file
unlink($file);
?>