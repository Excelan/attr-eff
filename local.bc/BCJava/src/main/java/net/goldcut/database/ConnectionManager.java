package net.goldcut.database;

import java.sql.*;
import java.io.*;
import java.util.HashMap;
import java.util.Properties;

/**
 * ConnectionManager (conn_pool simple autocommit, transacted)
 * Connection (sqlconn, in tx)
 */

public class ConnectionManager {

    /*
    //private static final String CONNECTION_HOST = "localhost";
    private static final String CONNECTION_HOST = "10.0.0.42";
    private static final String CONNECTION_DATABASE = "bc";
    private static final String CONNECTION_USER = "bc";
    private static final String CONNECTION_PASSWORD = "FFpS8ZBy3";
    private static final String CONNECTION_URL = "jdbc:postgresql://"+CONNECTION_HOST+"/"+CONNECTION_DATABASE;
    */

    public static Connection getConnectionForThread() {
        return ConnectionDispenser.getConnection();
    }

    public static String getConnectionURL()
    {
        //HashMap<String, String> props = new HashMap<>();

        FileInputStream fis;
        Properties property = new Properties();

        String CONNECTION_URL = "";

        try {
            //fis = new FileInputStream("src/main/resources/config.properties");
            fis = new FileInputStream("../../env.java.properties");
            property.load(fis);

            String CONNECTION_HOST = property.getProperty("db.host");
            String CONNECTION_DATABASE = property.getProperty("db.database");
            String CONNECTION_USER = property.getProperty("db.login");
            String CONNECTION_PASSWORD = property.getProperty("db.password");

            CONNECTION_URL = "jdbc:postgresql://"+CONNECTION_HOST+"/"+CONNECTION_DATABASE+"?user="+CONNECTION_USER+"&password="+CONNECTION_PASSWORD;

        } catch (IOException e) {
            System.err.println("ОШИБКА: Файл свойств env.java.properties отсуствует!");
            System.exit(1);
        }
        return CONNECTION_URL;
    }

    public static Connection getConnection() throws SQLException {
        //HashMap<String, String> props = getProps();
        try {
            Class.forName("org.postgresql.Driver");
        }
        catch (ClassNotFoundException e)
        {
            System.err.println("org.postgresql.Driver NOT FOUND IN CLASSPATH");
        }
        Connection connection = DriverManager.getConnection(getConnectionURL());
        connection.setAutoCommit(false);

        // https://docs.oracle.com/javase/7/docs/api/java/sql/Connection.html

        /**
        DatabaseMetaData dbmd = connection.getMetaData();
        if (dbmd.supportsTransactionIsolationLevel(Connection.TRANSACTION_SERIALIZABLE))
        {
            connection.setTransactionIsolation(Connection.TRANSACTION_SERIALIZABLE);
        }
        */

        System.out.println("********************** CONNECTED TO DB *************************************");

        return connection;
    }

}
