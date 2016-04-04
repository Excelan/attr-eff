package experiments.feature;


// PreparedStatement
// http://articles.javatalks.ru/articles/2
// Не всегда PreparedStatement кешируются с первого раза, часто их нужно выполнить по несколько раз.
// Подзапросы и запросы с UNION не кешируются
// Запросы внутри хранимых процедур не кешируются
// MySQL (не драйвер, а сам сервер) до версии 5.1.17 не кеширует запросы, у версий выше есть тоже свои “пасхальные яйца”, из-за который невозможно кешировать запрос, поэтому читайте обязательно документацию

// try-with-resources
// How should I use Java 7 try-with-resources to improve this code?
// http://docs.oracle.com/javase/tutorial/essential/exceptions/tryResourceClose.html
// http://stackoverflow.com/questions/8066501/how-should-i-use-try-with-resources-with-jdbc
// Not every caller is going to do the correct try {} finally {} stuff every time. Unfortunate, but true in most environments
// http://www.mastertheboss.com/jboss-server/jboss-datasource/using-try-with-resources-to-close-database-connections

// json update
// http://stackoverflow.com/questions/18209625/how-do-i-modify-fields-inside-the-new-postgresql-json-datatype
// This is coming in 9.5 in the form of jsonb_set by Andrew Dunstan based on an existing extension jsonbx that does work with 9.4
// http://www.pgxn.org/dist/jsonbx/1.0.0/
// http://www.postgresql.org/docs/9.5/static/functions-json.html
// http://michael.otacoo.com/postgresql-2/manipulating-jsonb-data-with-key-unique/

// TX, savepoints
// https://www3.ntu.edu.sg/home/ehchua/programming/java/JDBC_Intermediate.html
// http://www.cs.ait.ac.th/~on/O/oreilly/java-ent/servlet/ch09_04.htm
// http://stackoverflow.com/questions/3786568/nested-transactions-rollback-scenario
// http://www.postgresql.org/docs/9.3/static/sql-savepoint.html
// http://www.postgresql.org/docs/9.3/static/sql-release-savepoint.html
// http://www.postgresql.org/docs/9.3/static/sql-rollback-to.html


// select for update
// Even in the SERIALIZABLE isolation level, multiple selects can be made in parallel
// http://stackoverflow.com/questions/14789321/java-sql-connection-isolation-level

// auto map types to json
// https://gist.github.com/kdonald/2137988

// sqlexceptions
// https://docs.oracle.com/javase/tutorial/jdbc/basics/sqlexception.html

/*
public class DaoTools {
    static public Integer getInteger(ResultSet rs, String strColName) throws SQLException {
        int nValue = rs.getInt(strColName);
        return rs.wasNull() ? null : nValue;
    }
}


ARRAY[]
row.updateArray("nicknames",
    connection.createArrayOf("varchar", p.getNicknames().toArray()));
(p.getNicknames() returns a List<String>)

Array inArray = conn.createArrayOf("integer", new Integer[][] {{1,10},{2,20}});
stmt.setArray(1, inArray);

http://tonaconsulting.com/postgres-and-multi-dimensions-arrays-in-jdbc/

 */

import digital.erp.symbol.URN;
import net.goldcut.database.ConnectionManager;

import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class JDBCExample {

    private Connection connection;

    public void releaseConnection() {

    }

    public void startTransaction() {

    }

    public void commitTransaction() {

    }

    public void releaseTransaction() {

    }

    protected ConnectionManager create() {
        ConnectionManager c = new ConnectionManager();
        return c;
    }



    // getConnectionTransaction(txname)
//    public Connection getConnectionX() throws Exception {
        //this.init();
        //this.instance = new ConnectionManager();
        //return this.connection;
//    }

    private static ResultSet load(URN urn) throws Exception {
        try{
       // Connection conn = ConnectionManager.getConnection(); // conn.commit() ?
        Connection conn = ConnectionManager.getConnectionForThread();
        Statement stat = conn.createStatement();
        ResultSet rs = null;
        try {
            rs = stat.executeQuery("SELECT * FROM " + "urn.getPrototype().getOfType()" + " WHERE id = " + urn.getId().toString());
            /*
            try {
                while (rs.next()) {
                    Object o = rs.getInt(1);
                    System.out.println(o);
                    o = rs.getString(2);
                    System.out.println(o);
                    o = rs.getInt(3);
                    System.out.println(o);
                }
            } catch (Exception e) {
                System.out.println(e);
            } finally {
                rs.close();
            }
            */
        } catch (SQLException e) {
            System.err.println("ERROR load");
            System.out.println(e);
            conn.rollback();
        } finally {
            stat.close();
            conn.commit();
            // conn.close();
            return rs;
        }
    }finally {

        }
        }

    /*
    try(Connection con = getConnection()) {
       try (PreparedStatement prep = con.prepareConnection("Update ...")) {
           //prep.doSomething();
           //...
           //etc
           con.commit();
       } catch (SQLException e) {
           //any other actions necessary on failure
           con.rollback();
           //consider a re-throw, throwing a wrapping exception, etc
       }
    }
     */

    /*
    try (java.sql.Connection con = createConnection())
{
    con.setAutoCommit(false);
    try (Statement stm = con.createStatement())
    {
        stm.execute(someQuery); // causes SQLException
    }
    catch(SQLException ex)
    {
        con.rollback();
        con.setAutoCommit(true);
        throw ex;
    }
    con.commit();
    con.setAutoCommit(true);
}
     */

    /*
    public List<User> getUser(int userId) {
    String sql = "SELECT id, username FROM users WHERE id = ?";
    List<User> users = new ArrayList<>();
    try (Connection con = DriverManager.getConnection(myConnectionURL);
         PreparedStatement ps = con.prepareStatement(sql);) {
        ps.setInt(1, userId);
        try (ResultSet rs = ps.executeQuery();) {
            while(rs.next()) {
                users.add(new User(rs.getInt("id"), rs.getString("name")));
            }
        }
    } catch (SQLException e) {
        e.printStackTrace();
    }
    return users;
    }
    */

    /*
    public void updateCoffeeSales(HashMap<String, Integer> salesForWeek)
    throws SQLException {

    PreparedStatement updateSales = null;
    PreparedStatement updateTotal = null;

    String updateString =
        "update " + dbName + ".COFFEES " +
        "set SALES = ? where COF_NAME = ?";

    String updateStatement =
        "update " + dbName + ".COFFEES " +
        "set TOTAL = TOTAL + ? " +
        "where COF_NAME = ?";

    try {
        con.setAutoCommit(false);
        updateSales = con.prepareStatement(updateString);
        updateTotal = con.prepareStatement(updateStatement);

        for (Map.Entry<String, Integer> e : salesForWeek.entrySet()) {
            updateSales.setInt(1, e.getValue().intValue());
            updateSales.setString(2, e.getKey());
            updateSales.executeUpdate();
            updateTotal.setInt(1, e.getValue().intValue());
            updateTotal.setString(2, e.getKey());
            updateTotal.executeUpdate();
            con.commit();
        }
    } catch (SQLException e ) {
        JDBCTutorialUtilities.printSQLException(e);
        if (con != null) {
            try {
                System.err.print("Transaction is being rolled back");
                con.rollback();
            } catch(SQLException excep) {
                JDBCTutorialUtilities.printSQLException(excep);
            }
        }
    } finally {
        if (updateSales != null) {
            updateSales.close();
        }
        if (updateTotal != null) {
            updateTotal.close();
        }
        con.setAutoCommit(true);
    }
}

     */

}
