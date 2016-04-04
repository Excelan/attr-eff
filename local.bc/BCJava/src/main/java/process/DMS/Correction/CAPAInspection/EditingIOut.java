package process.DMS.Correction.CAPAInspection;

import digital.erp.data.Entity;
import digital.erp.domains.document.Document;
import digital.erp.domains.document.DocumentMetadata;
import digital.erp.process.ManagedProcessExecution;
import digital.erp.process.StageOut;
import net.goldcut.network.HttpRequest;
import net.goldcut.utils.Configuration;

public class EditingIOut implements StageOut {

    public void process(ManagedProcessExecution mpe)
    {
        try {
            DocumentMetadata dmd = Document.loadDocumentMetadata(mpe.getSubject());
            dmd.makePublic();
        } catch (Exception e) {
            e.printStackTrace();
        }

        /*
        // Tickets раздаем здесь (исполняет процесс уже Actor System User 0!!!)
        try {

            Integer trainingdocumentFieldValue;
            trainingdocumentFieldValue = Entity.directLoadIntegerForKeyIn("trainingdocument", mpe.getSubject());

            // TODO
            if (trainingdocumentFieldValue == 1) mpe.setNextstage("Doing");


        } catch (Exception e) {
            System.err.println("REMAP Delegates ERRROR");
            System.err.println(e.getMessage());
            e.printStackTrace();
        }

*/
    }


}
