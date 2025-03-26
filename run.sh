#!/usr/bin/env bash
php bin/init_db.php
systemfd --no-pid -s http::0.0.0.0:8080 -- php src/main.php
