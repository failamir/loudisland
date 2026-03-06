#!/bin/bash
# MySQL MASTER init script — creates replication user
# Runs once when container is first created

set -e

mysql -u root -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    -- Create replication user
    CREATE USER IF NOT EXISTS 'replicator'@'%' IDENTIFIED WITH mysql_native_password BY 'replicator_secret_2024';
    GRANT REPLICATION SLAVE ON *.* TO 'replicator'@'%';
    FLUSH PRIVILEGES;

    -- Show master status (useful for debugging)
    SHOW MASTER STATUS;
EOSQL

echo "[MASTER] Replication user created successfully."
