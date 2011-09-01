<?php

echo substr(sprintf("%o", @fileperms($_GET['f'])), -4);

?>