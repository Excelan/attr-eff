package process.DMS.Decisions.Reviewing;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import digital.erp.symbol.UPN;

import java.sql.SQLException;

public class ReviewOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("< OUT Review");
        //
        UPN parentMPEURN = mpe.getReturntopme();
        try {
            ManagedProcessExecution parendMPE = ManagedProcessExecution.load(parentMPEURN);
            // пробрасывание решения из процесса утверждения в вызывающий этап
            String decision = mpe.getMetadataValueByKey("decision");
            parendMPE.setMetadataKeyValue("decision", (decision == null) ? "approvedauto" : decision);
            //
            DocumentMetadata dmd = Document.loadDocumentMetadata(parendMPE.getSubject());
            dmd.makeDone();
        } catch (SQLException e) {
            e.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
