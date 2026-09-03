<?php
// Used by the installer SSL check to verify HTTPS is working.
preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
echo $match[0];
