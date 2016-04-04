#!/bin/sh

# DROP SCHEMA public CASCADE;
# create schema public;
# if you have permissions for that.  (It would delete functions and so on as well, not just tables.)


echo "Usage: script DBUSERNAME"

psql -U $1 -t -d $1 -c "SELECT 'DROP TABLE ' || n.nspname || '.\"' ||
c.relname || '\" CASCADE;' FROM pg_catalog.pg_class AS c LEFT JOIN
pg_catalog.pg_namespace AS n ON n.oid = c.relnamespace WHERE relkind =
'r' AND n.nspname NOT IN ('pg_catalog', 'pg_toast') AND
pg_catalog.pg_table_is_visible(c.oid)" >/tmp/droptables

cat /tmp/droptables

psql -U $1 -d $1 -f /tmp/droptables
