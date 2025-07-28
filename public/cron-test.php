<?php

preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
echo $match[0];
