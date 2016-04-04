package db

import java.sql.{SQLException, DriverManager, Connection, ResultSet, Statement}
import java.util

object PG {

    def getConnection(): Option[Connection] =
    {
        val driver = "org.postgresql.Driver"
        val url = "jdbc:postgresql://localhost:5432/ditest" // ?user=ditest&password=ditest
        val username = "ditest"
        val password = "ditest"

        var connection: Connection = null

        try {
            Class.forName(driver)
            connection = DriverManager.getConnection(url, username, password)
            connection.setAutoCommit(true)
            return Some(connection)
        }
        catch {
            case e: Throwable => e.printStackTrace
            return None
        }
    }

    def releaseConnection(connection: Connection): Unit = {
        connection.close
    }

    def doit(connection: Connection) {

        try {

            val q2 = connection.createStatement()
            val sql2 = "DROP TABLE ManagedProcess_Execution_Record"
            q2.executeUpdate(sql2)
            q2.close()
            val q3 = connection.createStatement()
            val sql3 = "DROP TABLE Claim"
            q3.executeUpdate(sql3)
            q3.close()
            val q4 = connection.createStatement()
            val sql4 = "DROP TABLE Protocol"
            q4.executeUpdate(sql4)
            q4.close()
        } catch {
            case e: Throwable => println(e)
        }


        try {
            val q = connection.createStatement()
            val sql = "CREATE TABLE Protocol " +
                "(ID BIGINT PRIMARY KEY NOT NULL," +
                " description TEXT NULL, " +

                " code VARCHAR(32), " +
                " state VARCHAR(42), " +
                " process VARCHAR(255), " +
                " parent VARCHAR(255), " +
                " children VARCHAR(255)[], " +
                " related VARCHAR(255)[], " +
                " initiator VARCHAR(128), " +
                " authors VARCHAR(255)[], " +
                " privatedraft BOOLEAN, " +
                " returned BOOLEAN, " +
                " done BOOLEAN, " +
                " archived BOOLEAN, " +
                " vised BOOLEAN, " +
                " approved BOOLEAN, " +

                " created DATE, " +
                " updated DATE ) "
            println(sql)
            q.executeUpdate(sql)
            q.close()
        } catch {
            case e: Throwable => println(e)
        }

        try {
            val q = connection.createStatement()
            val sql = "CREATE TABLE Claim " +
                "(ID BIGINT PRIMARY KEY NOT NULL," +
                " description TEXT NULL, " +
                " code VARCHAR(32), " +
                " state VARCHAR(42), " +
                " process VARCHAR(255), " +
                " parent VARCHAR(255), " +
                " children VARCHAR(255)[], " +
                " related VARCHAR(255)[], " +
                " initiator VARCHAR(128), " +
                " authors VARCHAR(255)[], " +
                " privatedraft BOOLEAN, " +
                " returned BOOLEAN, " +
                " done BOOLEAN, " +
                " archived BOOLEAN, " +
                " vised BOOLEAN, " +
                " approved BOOLEAN, " +
                " created DATE, " +
                " updated DATE ) "
            println(sql)
            q.executeUpdate(sql)
            q.close()
        } catch {
            case e: Throwable => println(e)
        }

        try {
            val q = connection.createStatement()
            val sql = "CREATE TABLE ManagedProcess_Execution_Record " +
                "(ID  BIGINT PRIMARY KEY     NOT NULL," +
                " initiator        VARCHAR(128), " +
                " PROTOTYPE           VARCHAR(90) NOT NULL, " +
                " returntopme            VARCHAR(120) NULL, " +
                " subject         VARCHAR(90) NULL," +
                " metadata       JSON, " +
                " done        BOOLEAN, " +
                " nextstage         VARCHAR(50) NULL," +
                " nextactor        VARCHAR(90) NULL, " +
                " currentstage         VARCHAR(50) NULL," +
                " currentactor        VARCHAR(90) NULL) "
            q.executeUpdate(sql)
            q.close()
        } catch {
            case e: Throwable => println(e)
        }

        /*
        try {
            val q = connection.createStatement()
            val sql = "INSERT INTO Protocol (id, children) VALUES ( 1, array['Abc','Def'] )"
            q.executeUpdate(sql)
            q.close()
        } catch {
            case e: Throwable => println(e)
        }

        try {
            // Configure to be Read Only
            //val statement = conn.createStatement(ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY)
            val q = connection.createStatement()
            val sql = "SELECT id, children FROM Protocol"
            val rs:ResultSet = q.executeQuery(sql)
            while ( rs.next() ) {
                val id = rs.getInt("id")
                val children = rs.getString("children")
                val childrenA = rs.getArray("children")
                val childrenO = rs.getObject("children")
                val c2 = childrenA.getArray()
                println(c2)
                println(childrenO)
                //println("X"+childrenA.getArray(1,1).toString())
//                val outputArray:Array = rs.getArray(1);
//                val realArray:String[] = (String[]) outputArray.getArray();
//                System.out.println(realArray.length + "-->" + util.Arrays.toString(realArray[0]));
            }
            rs.close()
            q.close()
        } catch {
            case e: Throwable => println(e)
        }
        */


    }
}