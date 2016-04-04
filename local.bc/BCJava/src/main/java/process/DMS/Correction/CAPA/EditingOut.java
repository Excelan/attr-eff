package process.DMS.Correction.CAPA;

import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class EditingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        try {
            //DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            //dmd.makePublic();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
