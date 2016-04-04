package process.DMS.Tenders.Tender;

import digital.erp.domains.document.Document;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageIn;
import digital.erp.symbol.Prototype;
import digital.erp.symbol.URN;
import digital.erp.symbol.URNExceptions;

import java.sql.SQLException;

public class CreateDraftIn implements StageIn {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! > Tender IN CREATE_DRAFT");
        try {

            // OLD MANUAL
            // URN subjectURN = Entity.createDraftBy(new URN(Prototype.fromString("ClaimsManagement:Claims:Claim")), 100); // initiator?

            // NEW GET FROM PROCESS START METADATA
            //String subjectPrototype = mpe.getMetadataValueByKey("subjectPrototype");
            String subjectPrototype = "Document:Tender:Extended";

            URN subjectURN = Document.createDraftForProcessBy(new URN(Prototype.fromString(subjectPrototype)), mpe.getUPN(), mpe.getCurrentactor());
            mpe.setSubject(subjectURN);
            mpe.saveSubject(subjectURN);

            // Additional metadata
            // mpe.setMetadataKeyValue("CreateDraftInVar","In");

        } catch (SQLException e) {
            e.printStackTrace();
        } catch (URNExceptions.IncorrectFormat incorrectFormat) {
            incorrectFormat.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
