<?php
// Sample callback fallback
file_put_contents("callback_log.txt", json_encode($_POST));
echo "Callback received";
?>