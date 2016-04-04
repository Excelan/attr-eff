package odt_new;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Created by anton on 21.01.16.
 */
public class OtherLoader {

    private static final String url = "jdbc:postgresql://127.0.0.1/bc";
    //127.0.0.1:5432
    private static final String user = "bc";
    private static final String password = "FFpS8ZBy3";

    public static void main(String args[]) {

        try {
            Connection connection = null;
            connection = DriverManager.getConnection(
                    url,user,password);
            System.out.println("Connection is sucsessful!");


        } catch (SQLException e) {

            System.out.println("Connection Failed! Check output console");
            e.printStackTrace();
            return;

        }
    }
}
