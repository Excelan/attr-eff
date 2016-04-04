package net.goldcut.database;

import java.sql.Connection;
import java.sql.SQLException;

public class ConnectionDispenser {

    private static class ThreadLocalConnection extends ThreadLocal {
        public Object initialValue() {
            try {
                return ConnectionManager.getConnection();
            } catch (SQLException e) {
                e.printStackTrace();
                return null;
            }
        }
    }

    private static ThreadLocalConnection conn = new ThreadLocalConnection();

    public static Connection getConnection() {
        return (Connection) conn.get();
    }
}
