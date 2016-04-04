package process.DMS.Regulation.Attestation;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

import java.sql.SQLException;

public class CreateDraftIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > Attestation IN CREATE_DRAFT");
        try {

            // Лист аттестации сессии ASR
            String subjectPrototype = "Document:Regulations:ASR";

            URN subjectURN = Document.createDraftForProcessBy(new URN(Prototype.fromString(subjectPrototype)), mpe.getUPN(), mpe.getCurrentactor());
            mpe.setSubject(subjectURN);
            mpe.saveSubject(subjectURN);

            // TODO link to SOP, TA
            // TODO Interval N usage?

            // if call from study - get TA from parent process?
            // if recursive run Attestation from Attestation - get TA from prev TA?

            URN sop = new URN(mpe.getMetadataValueByKey("sop"));
            URN ta = new URN(mpe.getMetadataValueByKey("ta"));
            Entity.directUpdateLong(subjectURN, "DocumentRegulationsSOP", sop.getId());
            Entity.directUpdateLong(subjectURN, "DocumentRegulationsTA", ta.getId());

            try {
                String json = "{ \"mpeId\":\"" + mpe.getUPN().getId() + "\", \"sopurn\":\"" + mpe.getMetadataValueByKey("sop") + "\" }";
                // TODO
                String gout = HttpRequest.postGetString(Configuration.host()+"/Process/Study/fillASRIterationFromSOPForTAInterval", json); // already there linked to SOP, TA!
                System.out.println("ROUTE OK " + gout);
            } catch (Exception e) {
                System.err.println("ROUTE ERROR");
                System.err.println(e.getMessage());
                //e.printStackTrace();
            }


        } catch (SQLException e) {
            e.printStackTrace();
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}