package digital.erp.domains.document;

import digital.erp.data.Entity;
import digital.erp.symbol.*;
import net.goldcut.database.ConnectionManager;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.sql.*;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.function.Function;
import java.util.stream.Collectors;

public class Document extends Entity implements EntityDomain {




    public static URN createDraftForProcessBy(URN urn, UPN processExecution, URN initiator) throws Exception {
        try {
      //  try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            // process?
            try (PreparedStatement insertST = conn.prepareStatement("INSERT INTO \"" + urn.getPrototype().getAlias() + "\" (id, code, privatedraft, returned, done, archived, initiator, state, process, created, updated, parent, children, related, version) VALUES (?, ?, true, false, false, false, ?, ?, ?, EXTRACT(EPOCH FROM NOW())::int, EXTRACT(EPOCH FROM NOW())::int, null, '{}', null, 1)")) {
                insertST.setLong(1, urn.getId());
                //insertST.setString(2, DocumentCodeFactory.codeForPrototype(urn.getPrototype(), urn.getId()));
                insertST.setNull(2, java.sql.Types.VARCHAR);
                if (initiator == null)
                    //insertST.setNull(3, java.sql.Types.VARCHAR);
                    insertST.setString(3, "urn:Actor:User:System:1");
                else
                    insertST.setString(3, initiator.toString());
                insertST.setString(4, "draft");
                insertST.setString(5, processExecution.toString());
                int i = insertST.executeUpdate();
                conn.commit();
                // System.out.println(">>> inserted " + i); //
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally {

        }

        //URN visaSheetURN = Entity.create(new URN(Prototype.fromString("DMS:DecisionSheet:Signed")));
        //Entity.directUpdateString(visaSheetURN, "document", urn.toString());

        // set Base Visants
        try {
            String json = "{\"subjectURN\":\"" + urn.toString() + "\"}";
            String gout = HttpRequest.postGetString(Configuration.host()+"/Process/SetBaseVisantsForDocument", json);
            System.out.println("BASE VISANTS OK " + gout);
        } catch (Exception e) {
            System.err.println("BASE VISANTS ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }


        return urn;
    }

    public static DocumentMetadata loadDocumentMetadata(URN urn) throws Exception {

        if (urn == null) throw new Exception("Document.loadDocumentMetadata(URN NULL)");

        DocumentMetadata emd = null;

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

         try{
        // try (Connection conn = ConnectionManager.getConnection()) {
            Connection conn = ConnectionManager.getConnectionForThread();
            //System.err.println(urn);
            String alias = urn.getPrototype().getAlias();
            String idstr = urn.getId().toString();
            try (
                    Statement stat = conn.createStatement();
                    ResultSet rs = stat.executeQuery("SELECT id, state, initiator, created, updated, vised, approved, process, parent, children, related, privatedraft, returned, done, archived, code FROM \"" + alias + "\" WHERE id = " + idstr)
            ) {
                while (rs.next()) {
                    // parent
                    URN parenturn = null;
                    if (rs.getString(9) != null && rs.getString(9).length() > 0) parenturn = new URN(rs.getString(9));
                    // children
                    List<URN> childrenURNs = new ArrayList<>();
                    Array dbArrayC = rs.getArray(10);
                    if (!rs.wasNull()) {
                        List<String> childrenURNsStr = new ArrayList<>();
                        String[] urnStringArrayC = (String[]) dbArrayC.getArray();
                        childrenURNsStr = Arrays.asList(urnStringArrayC);
                        childrenURNs = childrenURNsStr.stream().map(castStringToURN).collect(Collectors.toList());
                    }
                    // related
                    List<URN> relatedURNs = new ArrayList<>();
                    Array dbArray = rs.getArray(11);
                    if (!rs.wasNull()) {
                        List<String> relatedURNsStr = new ArrayList<>();
                        String[] urnStringArray = (String[]) dbArray.getArray();
                        relatedURNsStr = Arrays.asList(urnStringArray);
                        relatedURNs = relatedURNsStr.stream().map(castStringToURN).collect(Collectors.toList());
                    }
                    java.util.Date created = new java.util.Date(rs.getInt("created"));
                    java.util.Date updated = new java.util.Date(rs.getInt("updated"));
                    // fill DocumentMetadata
                    emd = new DocumentMetadata(urn, rs.getString("code"), rs.getString("state"), new URN(rs.getString("initiator")), created, updated, rs.getBoolean("privatedraft"), rs.getBoolean("returned"), rs.getBoolean("done"), rs.getBoolean("archived"), rs.getBoolean("vised"), rs.getBoolean("approved"), new UPN(rs.getString("process")), parenturn, childrenURNs, relatedURNs);
                }
                conn.commit();
            } catch (SQLException e) {
                conn.rollback();
                throw e;
            }
        } finally{

         }
        return emd;
    }

}
