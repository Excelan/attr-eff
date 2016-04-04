package digital.erp.process;

import digital.erp.symbol.Prototype;
import digital.erp.symbol.UPN;
import digital.erp.symbol.URN;
import net.goldcut.database.ConnectionManager;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import static java.lang.Math.toIntExact;

public class Journaling {

    public enum Direction {
        IN,
        OUT
    }

    protected static void record(ManagedProcessExecution runnedprocess, URN subject, String stage, URN actor, Direction direction, String metadata) {
        if (metadata == null) metadata = "{}";
        try{
       // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            try (PreparedStatement insertST = conn.prepareStatement("INSERT INTO \"ManagedProcess_Journal_Record\" (id, \"ManagedProcessExecutionRecord\", stagedirection, operationtime, stage, actor, metadata, subject) VALUES (?, ?, ?, NOW(), ?, ? , '"+metadata+"', ?)")) {
                insertST.setLong(1, URN.randomLong());
                insertST.setInt(2, toIntExact(runnedprocess.getUPN().getId()));
                insertST.setInt(3, direction == Direction.IN ? 1 : 2);
                //insertST.setString(4, stage.getName());
                insertST.setString(4, stage);
                if (actor == null)
                    insertST.setNull(5, java.sql.Types.VARCHAR);
                else
                    insertST.setString(5, actor.toString());
                insertST.setString(6, subject.toString());    
                int i = insertST.executeUpdate();
                conn.commit();
                //System.out.println(">>> inserted new JOURNAL " + i + insertST.toString());
            } catch (SQLException e) {
                e.printStackTrace();
                conn.rollback();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

}
