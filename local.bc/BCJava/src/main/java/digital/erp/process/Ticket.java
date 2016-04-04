package digital.erp.process;

import digital.erp.data.Entity;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;
import net.goldcut.database.ConnectionManager;

import java.io.IOException;
import java.sql.*;
import java.util.Random;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;


public class Ticket {

    private URN urn;
    private boolean isvalid = false;
    private boolean allowopen = false;
    private boolean allowsave = false;
    private boolean allowcomplete = false;
    private boolean allowcomment = false;
    private boolean allowreadcomments = false;
    private boolean allowknowcuurentstage = false;
    private boolean allowseejournal = false;
    private boolean allowearly = false;



    public String toString()
    {
        return urn.toString() + " " + isvalid + " " + allowopen + " " + allowcomplete;
    }

    public static boolean activateAllRightsFor(Long tickedId)
    {
        boolean done = false;
         try{
      //  try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"Feed_MPETicket_InboxItem\" SET isvalid = ?, allowknowcuurentstage = ?, allowseejournal = ?, allowearly = ?, allowopen = ?, allowsave = ?, allowcomplete = ?, allowcomment = ?, allowreadcomments = ? WHERE id = ?")) {
                updateST.setInt(1, 1);
                updateST.setInt(2, 1);
                updateST.setInt(3, 1);
                updateST.setInt(4, 1);
                updateST.setInt(5, 1);
                updateST.setInt(6, 1);
                updateST.setInt(7, 1);
                updateST.setInt(8, 1);
                updateST.setInt(9, 1);
                updateST.setLong(10, tickedId);
                updateST.executeUpdate();
                conn.commit();
                done = true;
            } catch (SQLException e) {
                e.printStackTrace();
                conn.rollback();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return done;
    }

    public static Long createWithoutRightsForActorProcess(URN actor, UPN upn)
    {
        Long id = null;
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement updateST = conn.prepareStatement("INSERT INTO \"Feed_MPETicket_InboxItem\" (id, \"ManagementPostIndividual\", \"ManagedProcessExecutionRecord\", isvalid, activateat, created) VALUES (?,?,?,?, NOW(), EXTRACT(EPOCH FROM NOW())::int)")) {
                id = URN.randomLong();
                updateST.setLong(1, id);
                updateST.setLong(2, actor.getId());
                updateST.setLong(3, upn.getId());
                updateST.setInt(4, 1);
                int i = updateST.executeUpdate();
                conn.commit();
                System.out.println("createWithoutRightsForActorProcess insert: "+i);
            } catch (SQLException e) {
                e.printStackTrace();
                conn.rollback();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return id;
    }

    public static Long createAndActivateAllRightsForActorProcess(URN actor, UPN upn)
    {
        Long id = null;
        try{
        //try (Connection conn = ConnectionManager.getConnection()) {
        Connection conn = ConnectionManager.getConnectionForThread();
        try {
            try (PreparedStatement updateST = conn.prepareStatement("INSERT INTO \"Feed_MPETicket_InboxItem\" (id, \"ManagementPostIndividual\", \"ManagedProcessExecutionRecord\", isvalid, allowknowcuurentstage, allowseejournal, allowearly, allowopen, allowsave, allowcomplete, allowcomment, allowreadcomments, activateat, created) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, NOW(), EXTRACT(EPOCH FROM NOW())::int)")) {
                id = URN.randomLong();
                updateST.setLong(1, id);
                updateST.setLong(2, actor.getId());
                updateST.setLong(3, upn.getId());
                updateST.setInt(4, 1);
                updateST.setInt(5, 1);
                updateST.setInt(6, 1);
                updateST.setInt(7, 1);
                updateST.setInt(8, 1);
                updateST.setInt(9, 1);
                updateST.setInt(10, 1);
                updateST.setInt(11, 1);
                updateST.setInt(12, 1);
                int i = updateST.executeUpdate();
                conn.commit();
                System.out.println("Tickets createAndActivateAllRightsForActorProcess insert: "+i);
            } catch (SQLException e) {
                e.printStackTrace();
                conn.rollback();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return id;
    } finally {

        }
        }

    public static Integer getTotalCountForProcess(UPN upn)
    {
        Integer total = null;
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT count(id) as total FROM \"Feed_MPETicket_InboxItem\" WHERE \"ManagedProcessExecutionRecord\" = " + upn.getId())
            ) {
                if (rs.next()) {
                    total = rs.getInt("total");
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            } catch (Exception e) {
                e.printStackTrace();
            }
            conn.commit();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return total;
    }

    public static Integer getTotalCount()
    {
        Integer total = null;
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT count(id) as total FROM \"Feed_MPETicket_InboxItem\"")
            ) {
                if (rs.next()) {
                    total = rs.getInt("total");
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            } catch (Exception e) {
                e.printStackTrace();
            }
            conn.commit();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return total;
    }

    public static Ticket loadForProcessCurrentActor(UPN upn, URN actor)
    {
        Ticket ticket = null;
          try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id FROM \"Feed_MPETicket_InboxItem\" WHERE \"ManagementPostIndividual\" = " + actor.getId() + " AND \"ManagedProcessExecutionRecord\" = " + upn.getId())
            ) {
                if (rs.next()) {
                    ticket = new Ticket(rs.getLong("id"));
                }
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            } catch (Exception e) {
                e.printStackTrace();
            }
            conn.commit();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ticket;
    }

    public static void closeAllFor(UPN upn)
    {
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id, \"ManagementPostIndividual\" FROM \"Feed_MPETicket_InboxItem\" WHERE \"ManagedProcessExecutionRecord\" = " + upn.getId() + " ");
            ) {
                // активация тикета, созданного ранее (актер уже был в процессе на прошлых шагах или мы вернулись к прошлым шагам)
                while (rs.next()) {
                    Long tickedId = rs.getLong("id");
                    //Long managementPostIndividualId = rs.getLong("ManagementPostIndividual"); // ???
                    try (PreparedStatement updateST = conn.prepareStatement("UPDATE \"Feed_MPETicket_InboxItem\" SET isvalid = ? WHERE id = ?")) {
                        updateST.setLong(1, 0);
                        updateST.setLong(2, tickedId);
                        updateST.executeUpdate();
                        conn.commit();
                    } catch (SQLException e) {
                        conn.rollback();
                        e.printStackTrace(); // + Log error
                    }
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public Ticket(Long id) throws Exception {
        try {
            this.urn = new URN("URN:Feed:MPETicket:InboxItem:" + id);
            try{
           // try (Connection conn = ConnectionManager.getConnection()) {
                Connection conn = ConnectionManager.getConnectionForThread();
                try (
                        Statement stat = conn.createStatement();
                        ResultSet rs = stat.executeQuery("SELECT * FROM \"Feed_MPETicket_InboxItem\" WHERE id = " + id)
                ) {
                    while (rs.next()) {
                        this.isvalid = rs.getInt("isvalid") == 1;
                        this.allowopen = rs.getInt("allowopen") == 1;
                        this.allowsave = rs.getInt("allowsave") == 1;
                        this.allowcomplete = rs.getInt("allowcomplete") == 1;
                        this.allowcomment = rs.getInt("allowcomment") == 1;
                        this.allowreadcomments = rs.getInt("allowreadcomments") == 1;
                        this.allowknowcuurentstage = rs.getInt("allowknowcuurentstage") == 1;
                        this.allowseejournal = rs.getInt("allowseejournal") == 1;
                        this.allowearly = rs.getInt("allowearly") == 1;
                    }
                    conn.commit();
                } catch (SQLException e) {
                    conn.rollback();
                }
            } catch (SQLException e) {
                e.printStackTrace();
            }
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        }
        if (this.urn == null) throw new Exception("Ticken construct failed");
    }

    public boolean isvalid() {
        return isvalid;
    }

    public void setIsvalid(boolean isvalid) {
        this.isvalid = isvalid;
        try {
            Entity.directUpdateInteger(urn, "isvalid", isvalid ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowopen() {
        return allowopen;
    }

    public void setAllowopen(boolean allowopen) {
        this.allowopen = allowopen;
        try {
            Entity.directUpdateInteger(urn, "allowopen", allowopen ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowcomplete() {
        return allowcomplete;
    }

    public void setAllowcomplete(boolean allowcomplete) {
        this.allowcomplete = allowcomplete;
        try {
            Entity.directUpdateInteger(urn, "allowcomplete", allowcomplete ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowsave() {
        return allowsave;
    }

    public void setAllowsave(boolean allowsave) {
        this.allowsave = allowsave;
        try {
            Entity.directUpdateInteger(urn, "allowsave", allowsave ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowcomment() {
        return allowcomment;
    }

    public void setAllowcomment(boolean allowcomment) {
        this.allowcomment = allowcomment;
        try {
            Entity.directUpdateInteger(urn, "allowcomment", allowcomment ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowreadcomments() {
        return allowreadcomments;
    }

    public void setAllowreadcomments(boolean allowreadcomments) {
        this.allowreadcomments = allowreadcomments;
        try {
            Entity.directUpdateInteger(urn, "allowreadcomments", allowreadcomments ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowknowcuurentstage() {
        return allowknowcuurentstage;
    }

    public void setAllowknowcuurentstage(boolean allowknowcuurentstage) {
        this.allowknowcuurentstage = allowknowcuurentstage;
        try {
            Entity.directUpdateInteger(urn, "allowknowcuurentstage", allowknowcuurentstage ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowseejournal() {
        return allowseejournal;
    }

    public void setAllowseejournal(boolean allowseejournal) {
        this.allowseejournal = allowseejournal;
        try {
            Entity.directUpdateInteger(urn, "allowseejournal", allowseejournal ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public boolean isAllowearly() {
        return allowearly;
    }

    public void setAllowearly(boolean allowearly) {
        this.allowearly = allowearly;
        try {
            Entity.directUpdateInteger(urn, "allowearly", allowearly ? 1 : 0);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


}
