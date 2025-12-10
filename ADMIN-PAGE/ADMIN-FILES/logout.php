<?php

session_start();
session_unset();
session_destroy();

echo "<script>
  alert('You have been logged out successfully.');
  window.location.href = '../../LOGS/LOGS-FILES/login.php';
</script>";
exit;
?>