#!/bin/sh
~/Dropbox/bin/dropDatabaseTablesPG.sh bc && psql -U bc bc < bc3.sql
