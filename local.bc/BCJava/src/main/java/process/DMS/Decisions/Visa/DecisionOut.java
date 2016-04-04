package process.DMS.Decisions.Visa;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import digital.erp.symbol.UPN;

import java.sql.SQLException;

public class DecisionOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("DMS Visa Decision Out");
        //
        UPN parentMPEURN = mpe.getReturntopme();
        try {
            ManagedProcessExecution parendMPE = ManagedProcessExecution.load(parentMPEURN);
            // пробрасывание решения из процесса визирования в вызывающий этап
            String decision = mpe.getMetadataValueByKey("decision");
            parendMPE.setMetadataKeyValue("decision", (decision == null) ? "visedauto" : decision);
            //
            // Entity.directUpdateBoolean(parendMPE.getSubject(), "vised", true);
            DocumentMetadata dmd = Document.loadDocumentMetadata(parendMPE.getSubject());
            dmd.makeVised();
        } catch (SQLException e) {
            e.printStackTrace();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}