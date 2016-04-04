package digital.erp.data;

import digital.erp.domains.document.DocumentMetadata;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;
import net.goldcut.database.ConnectionManager;

import java.sql.*;
import java.util.*;
import java.util.function.Function;
import java.util.stream.Collectors;

public class Entity {


    public static URN create(URN urn) throws Exception {
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
              Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement insertST = conn.prepareStatement("INSERT INTO \"" + urn.getPrototype().getAlias() + "\" (id, created) VALUES (?, EXTRACT(EPOCH FROM NOW())::int)")) {
                insertST.setLong(1, urn.getId());
                int i = insertST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

        }
        return urn;
    }

    public static URN createDraftBy(URN urn, URN initiator) throws Exception {
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement insertST = conn.prepareStatement("INSERT INTO \"" + urn.getPrototype().getAlias() + "\" (id, initiator, state, created, updated) VALUES (?, ?, ?, EXTRACT(EPOCH FROM NOW())::int, EXTRACT(EPOCH FROM NOW())::int)")) {
                insertST.setLong(1, urn.getId());
                insertST.setString(2, initiator.toString());
                insertST.setString(3, "draft"); // NULL state
                insertST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally {

        }
        return urn;
    }

    public static void directUpdateString(URN urn, String field, String value) throws Exception {
         try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = ? WHERE id = ?")) {
                if (value == null)
                    updateST.setNull(1, java.sql.Types.VARCHAR);
                else
                    updateST.setString(1, value);
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally {

         }
    }

    public static void directUpdateBoolean(URN urn, String field, Boolean value) throws Exception {
         try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = ? WHERE id = ?")) {
                if (value == null)
                    updateST.setNull(1, Types.BOOLEAN);
                else
                    updateST.setBoolean(1, value);
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
                //System.out.println(">>> updated " + i); //
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

         }
    }

    public static void directUpdateLong(URN urn, String field, Long value) throws Exception {
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = ? WHERE id = ?")) {
                if (value == null)
                    updateST.setNull(1, Types.BIGINT);
                else
                    updateST.setLong(1, value);
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
                //System.out.println(">>> updated " + i); //
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

        }
    }

    public static void directUpdateInteger(URN urn, String field, Integer value) throws Exception {
         try{
        // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = ? WHERE id = ?")) {
                if (value == null)
                    updateST.setNull(1, Types.INTEGER);
                else
                    updateST.setInt(1, value);
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
                //System.out.println(">>> updated " + i); //
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

         }
    }

    public static void directArrayAppendString(URN urn, String field, String value) throws Exception {
        if (value == null) throw new NullPointerException("In directArrayAppendString value must be not null");
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            String sql = "UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = array_append(\"" + field + "\", ?) WHERE id = ?";
            try (PreparedStatement updateST = conn.prepareStatement(sql)) {
                updateST.setString(1, value);
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                System.err.println(sql);
                throw e;
            }
        } finally{

        }
    }

    public static void directArrayClear(URN urn, String field) throws Exception {
         try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = '{}' WHERE id = ?")) {
                updateST.setLong(2, urn.getId());
                int i = updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{}
    }

    public static void directArrayRemoveString(URN urn, String field, String value) throws Exception {

        if (value == null) throw new NullPointerException("In directArrayRemoveString value must be not null");
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \"" + field + "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                List<String> filtered = new ArrayList<>();
                while (rs.next()) {

                    Array dbArrayC = rs.getArray(1);
                    if (!rs.wasNull()) {
                        List<String> fieldarrayval = new ArrayList<>();
                        String[] fieldarraystr = (String[]) dbArrayC.getArray();
                        fieldarrayval = Arrays.asList(fieldarraystr);
                        filtered = fieldarrayval.stream().filter(u -> !u.equals(value)).collect(Collectors.toList());
                    }
                }
                try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + field + "\" = ? WHERE id = ?")) {
                    Array inArray = conn.createArrayOf("varchar", filtered.toArray());
                    updateST.setArray(1, inArray);
                    updateST.setLong(2, urn.getId());
                    int i = updateST.executeUpdate();
                   // conn.commit();
                } catch (SQLException e) {
                   // conn.rollback();
                    throw e;
                }
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{}
    }

    public static String directLoadStringForKeyIn(String key, URN urn) throws Exception {
        String str = null;
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                if (rs.next()) {
                    str = rs.getString(key);
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally {

        }
        return str;
    }

    public static java.util.Date directLoadDateForKeyIn(String key, URN urn) throws Exception {
        java.util.Date d = null;
        try{
            // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                if (rs.next()) {
                    d = rs.getDate(key);
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally {

        }
        return d;
    }

    public static Integer directLoadIntegerForKeyIn(String key, URN urn) throws Exception {
        Integer result = null;
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                if (rs.next()) {
                    result = rs.getInt(key);
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally {

        }
        return result;
    }

    public static Long directLoadLongForKeyIn(String key, URN urn) throws Exception {
        Long result = null;
        try{
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                if (rs.next()) {
                    result = rs.getLong(key);
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally {

        }
        return result;
    }

    public static void directArrayExistsURN(URN urn, String field, URN value) throws Exception {
        if (value == null) throw new NullPointerException("In directListExistsURN value must be not null");
        try {
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            String f  = field + "_" + value.getPrototype().getShortName();
            String sql = "SELECT count(id) AS edgecount \"" + urn.getPrototype().getAlias() + "\" WHERE ? = ANY(\"" + f + "\") WHERE id = ?";
            try (PreparedStatement updateST = conn.prepareStatement(sql)) {
                updateST.setString(1, value.toString()); // есть ли этот urn в списке?
                updateST.setLong(2, urn.getId()); // в каком объекте в списке смотрим
                updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                System.err.println(sql);
                throw e;
            }
        } finally{

        }
    }

    public static List<URN> directLoadURNListForKeyIn(String key, URN urn) throws Exception {
        Function<String, URN> castStringToURN;
        castStringToURN = new Function<String, URN>() {
            public URN apply(String s) {
                URN urn = null;
                try {
                    urn = new URN(s);
                } catch (URNExceptions.IncorrectFormat incorrectFormat) {
                    System.err.println(incorrectFormat);
                    incorrectFormat.printStackTrace();
                }
                return urn;
            }
        };
        List<URN> result = null;
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                List<String> filtered = new ArrayList<>();
                while (rs.next()) {
                    Array dbArrayC = rs.getArray(1);
                    if (!rs.wasNull()) {
                        List<String> childrenURNsStr = new ArrayList<>();
                        String[] urnStringArrayC = (String[]) dbArrayC.getArray();
                        //System.out.println(urnStringArrayC);
                        childrenURNsStr = Arrays.asList(urnStringArrayC);
                        //System.out.println(childrenURNsStr);
                        result = childrenURNsStr.stream().map(castStringToURN).collect(Collectors.toList());
                    }
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally{}
        return result;
    }

    public static List<URN> directLoadURNListForKeyIn(String key, Prototype resultPrototype, URN urn) throws Exception {
        List<URN> result = null;
        try{
            // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT \""+key+"_"+resultPrototype.getShortName()+ "\" FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                //List<String> filtered = new ArrayList<>();
                while (rs.next()) {
                    Array dbArrayC = rs.getArray(1);
                    if (!rs.wasNull()) {
                        List<Integer> childrenURNsStr = new ArrayList<>();
                        Integer[] urnStringArrayC = (Integer[]) dbArrayC.getArray();
                        //System.out.println(urnStringArrayC);
                        childrenURNsStr = Arrays.asList(urnStringArrayC);
                        //System.out.println(childrenURNsStr);
                        result = childrenURNsStr.stream().map(uuid -> new URN(resultPrototype, new Long(uuid))).collect(Collectors.toList());
                    }
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally{}
        return result;
    }

    public static void directListAppendURN(URN urn, String field, URN value) throws Exception {
        if (value == null) throw new NullPointerException("In directListAppendURN value must be not null");
        try {
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            String f  = field + "_" + value.getPrototype().getShortName();
            String sql = "UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + f + "\" = array_append(\"" + f + "\", ?) WHERE id = ?";
            try (PreparedStatement updateST = conn.prepareStatement(sql)) {
                updateST.setInt(1, value.getId().intValue());
                updateST.setLong(2, urn.getId());
                updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                System.err.println(sql);
                throw e;
            }
        } finally{

        }
    }

    public static void directListRemoveURN(URN urn, String field, URN value) throws Exception {
        if (value == null) throw new NullPointerException("In directListRemoveURN value must be not null");
        try {
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            String f  = field + "_" + value.getPrototype().getShortName();
            String sql = "UPDATE \"" + urn.getPrototype().getAlias() + "\" SET \"" + f + "\" = array_remove(\"" + f + "\", ?) WHERE id = ?";
            try (PreparedStatement updateST = conn.prepareStatement(sql)) {
                updateST.setInt(1, value.getId().intValue());
                updateST.setLong(2, urn.getId());
                updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                System.err.println(sql);
                throw e;
            }
        } finally{

        }
    }

    public static void directListExistsURN(URN urn, String field, URN value) throws Exception {
        if (value == null) throw new NullPointerException("In directListExistsURN value must be not null");
        try {
            //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            String f  = field + "_" + value.getPrototype().getShortName();
            String sql = "SELECT count(id) AS edgecount \"" + urn.getPrototype().getAlias() + "\" WHERE ? = ANY(\"" + f + "\") WHERE id = ?";
            try (PreparedStatement updateST = conn.prepareStatement(sql)) {
                updateST.setInt(1, value.getId().intValue()); // есть ли этот urn в списке?
                updateST.setLong(2, urn.getId()); // в каком объекте в списке смотрим
                updateST.executeUpdate();
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                System.err.println(sql);
                throw e;
            }
        } finally{

        }
    }


    public static EntityMetadata loadEntityMetadata(URN urn) throws Exception {
        EntityMetadata emd = null;
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id, state, initiator, created, updated FROM \"" + urn.getPrototype().getAlias() + "\" WHERE id = " + urn.getId().toString())
            ) {
                if (rs.next()) {
                    emd = new EntityMetadata(urn, rs.getString("state"), new URN(rs.getString("initiator")), new java.util.Date(rs.getInt("created")), new java.util.Date(rs.getInt("updated")));
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
            conn.commit(); // after result set is closed
        } finally{}
        return emd;
    }


}
