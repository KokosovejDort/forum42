<?php
  $db = new PDO('mysql:host=127.0.0.1;dbname=DBNAME;charset=utf8', 'dBNAME', 'password');

  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
