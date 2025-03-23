#!/usr/bin/env bash
systemfd --no-pid -s http::8080 -- php src/main.php
