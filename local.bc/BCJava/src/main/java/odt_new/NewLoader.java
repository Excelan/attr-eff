package odt_new;

import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Created by anton on 21.01.16.
 */
public class NewLoader {

    private static final String CONNECTION_HOST = "localhost";
    private static final String CONNECTION_DATABASE = "bc";
    private static final String CONNECTION_USER = "bc";
    private static final String CONNECTION_PASSWORD = "FFpS8ZBy3";
    private static final String CONNECTION_URL = "jdbc:postgresql://"+CONNECTION_HOST+"/"+CONNECTION_DATABASE;


    public static Connection getConnection() throws SQLException {
        try {
            Class.forName("org.postgresql.Driver");
        }
        catch (ClassNotFoundException e)
        {
            System.err.println("org.postgresql.Driver NOT FOUND IN CLASSPATH");
        }
        Connection connection = DriverManager.getConnection(CONNECTION_URL, CONNECTION_USER, CONNECTION_PASSWORD);
        connection.setAutoCommit(false);
        // https://docs.oracle.com/javase/7/docs/api/java/sql/Connection.html
        DatabaseMetaData dbmd = connection.getMetaData();
        System.out.println("Database works!");

        /*
        if (dbmd.supportsTransactionIsolationLevel(Connection.TRANSACTION_SERIALIZABLE))
        {
            connection.setTransactionIsolation(Connection.TRANSACTION_SERIALIZABLE);
        }
        */

        return connection;
    }

}
