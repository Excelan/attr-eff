package process.DMS.Tenders.Tender;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class Tour1_Step1Out implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        try {
            DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            dmd.makePublic();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
