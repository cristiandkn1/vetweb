<?php
$ticket = trim($_GET['ticket'] ?? '');
header("Location: ../ticket.php?ticket=" . urlencode($ticket));
exit;
