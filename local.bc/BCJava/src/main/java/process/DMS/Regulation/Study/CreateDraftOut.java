package process.DMS.Regulation.Study;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;

public class CreateDraftOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        String sopURNstr = mpe.getMetadataValueByKey("sop");
        if (sopURNstr == null) try {
            throw new Exception("NO SOP IN METADATA");
        } catch (Exception e) {
            System.err.println(e.getMessage());
            e.printStackTrace();
        }
    }

}
