<?php

require_once 'providers/auth_provider.php';

logout();
header("Location: index.php");
exit();
