<?php
// public/index.php - Redirects to home.php

// This ensures a clean redirect without any output before the header.
header("Location: home.php");
exit; // Always exit after a header redirect
?>