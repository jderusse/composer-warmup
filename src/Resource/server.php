<?php

if (isset($_GET['file'])) {
    opcache_compile_file($_GET['file']);
}
