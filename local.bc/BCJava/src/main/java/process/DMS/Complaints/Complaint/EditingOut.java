package process.DMS.Complaints.Complaint;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class EditingOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        System.out.println("!!! < STAGE OUT EDIT < PROCESS");

        /*
        try {
            Entity.directArrayAppendString(mpe.getSubject(), "children", "URN:D:B:C:777");
            Entity.directArrayAppendString(mpe.getSubject(), "children", "URN:D:A:A:123");
            Entity.directArrayRemoveString(mpe.getSubject(), "children", "URN:D:B:C:777");
        } catch (Exception e) {
            e.printStackTrace();
        }
        */


        try {
            DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            dmd.makePublic();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

}
