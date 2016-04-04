package process.DMS.Complaints.Detective;

import digital.erp.data.Entity;
import digital.erp.data.EntityMetadata;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageProcessing;

public class ProtocolCreateDraftProcessing implements StageProcessing {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("= Detective.ProtocolCreateDraft");
        try {

            EntityMetadata emd = Entity.loadEntityMetadata(mpe.getSubject());
            System.out.println(emd);

            DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            System.out.println(dmd);

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
