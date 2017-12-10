#!/bin/bash

bin/gps-tool migrate --wipe
bin/gps-tool add-user vearutop "Vea Rutop"
bin/gps-tool add-story --login vearutop --name nepal17 --title "Nepal 2017" --timezone "Asia/Kathmandu" --time-to "2017-11-11 16:00:00"
bin/gps-tool import-gpx vearutop ./data/
bin/gps-tool import-photos vearutop nepal17 ./data/nepal17